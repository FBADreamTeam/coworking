<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 31/07/2018
 * Time: 14:50.
 */

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Booking;
use App\Entity\BookingOptions;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Room;
use App\Entity\RoomType;
use App\Form\AddressType;
use App\Form\BookingAddOptionsType;
use App\Form\OrderType;
use App\Managers\RoomManager;
use App\Services\BookingPriceCalculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class BookingController.
 *
 * @Route("/booking")
 */
class BookingController extends Controller
{
    /**
     * @Route("/", name="booking_index")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $roomTypes = $em->getRepository(RoomType::class)->findAll();

        $data = [];

        /** @var RoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $data[$roomType->getId()]['roomType'] = $roomType;
            $data[$roomType->getId()]['rooms'] = $em->getRepository(Room::class)->findBy(['roomType' => $roomType]);
        }

        return $this->render('booking/booking_index.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/filter", name="booking_filter")
     * @Method("GET")
     *
     * @param Request     $request
     * @param RoomManager $roomManager
     *
     * @return string|RedirectResponse
     */
    public function filter(Request $request, RoomManager $roomManager)
    {
        if ($request->isXmlHttpRequest()) {
            // Filter rooms by availability
//            $rooms = $roomManager->filter($request->get('startDate'), $request->get('endDate'));
            $data = [];

            // get all types of rooms
            $types = $this->getDoctrine()->getRepository(RoomType::class)->findAll();

            /** @var RoomType $type */
            foreach ($types as $type) {
                $data[$type->getId()] = $roomManager->filterByType($type, $request->get('startDate'), $request->get('endDate'));
            }

            return new JsonResponse(['rooms' => $roomManager->serialize($data)]);
        } else {
            $this->addFlash('notice', 'Sorry, unauthorized action');

            return $this->redirectToRoute('booking_index');
        }
    }

    /**
     * @Route("/options", name="booking_options")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param TranslatorInterface $translator
     *
     * @param BookingPriceCalculator $calculator
     * @param SerializerInterface $serializer
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function selectOptions(Request $request, SessionInterface $session, TranslatorInterface $translator, BookingPriceCalculator $calculator, SerializerInterface $serializer)
    {
        $em = $this->getDoctrine()->getManager();

        // request comes from calendar view, expects an url
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            // set the room and date values in session
            $session->set('roomId', $request->get('roomId'));
            $session->set('startDate', $request->get('startDate'));
            $session->set('endDate', $request->get('endDate'));

            // return url to form
            return new JsonResponse(['url' => $this->generateUrl('booking_options')]);
        }

        // request comes from redirecting from ajax, expects a form
        if (!$request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $booking = new Booking();

            $startDateTime = new \DateTime($session->get('startDate'));
            $endDateTime = new \DateTime($session->get('endDate'));

            // finding the room
            /** @var Room $room */

            $room = $em->getRepository(Room::class)->find($session->get('roomId'));
            $booking->setRoom($room);
            $booking->setStartDate($startDateTime);
            $booking->setEndDate($endDateTime);

            // getting the roomOptions
            $roomOptions = $room->getRoomType()->getRoomOptions();

            // creating bookingOptions
            foreach ($roomOptions as $roomOption) {
                $bookingOption = new BookingOptions();
                $bookingOption->setRoomOption($roomOption);
                $booking->addBookingOption($bookingOption);
            }

            $priceWO = $calculator->calculateTotalPriceWithoutOptions($booking);
            $booking->setTotalHTWithoutOptions($priceWO);

            $form = $this->createForm(BookingAddOptionsType::class, $booking);

            return $this->render('booking/options_form.html.twig', [
                'interval'          => $startDateTime->diff($endDateTime),
                'room'              => $room,
                'booking'           => $booking,
                'encodedOptions'    => $serializer->serialize($booking->getRoomOptionsAsHashedArray(), 'json', ['groups' => 'options']),
                'form'              => $form->createView(),
            ]);
        }

        // request comes from POST, payload is a form with booking options
        if (!$request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $booking = new Booking();

            $startDateTime = new \DateTime($session->get('startDate'));
            $endDateTime = new \DateTime($session->get('endDate'));

            // finding the room
            /** @var Room $room */
            $room = $em->getRepository(Room::class)->find($session->get('roomId'));
            $booking->setRoom($room);
            $booking->setStartDate($startDateTime);
            $booking->setEndDate($endDateTime);

            // getting the roomOptions
            $roomOptions = $room->getRoomType()->getRoomOptions();

            // creating bookingOptions
            foreach ($roomOptions as $roomOption) {
                $bookingOption = new BookingOptions();
                $bookingOption->setRoomOption($roomOption);
                $booking->addBookingOption($bookingOption);
            }

            $form = $this->createForm(BookingAddOptionsType::class, $booking)
                ->handleRequest($request);

            $priceWO = $calculator->calculateTotalPriceWithoutOptions($booking);

            if ($form->isSubmitted() && $form->isValid()) {
                // check all the options
                foreach ($booking->getBookingOptions() as $bookingOption) {
                    // if quantity is null || 0, we remove the option
                    $quantity = $bookingOption->getQuantity();
                    if (null === $quantity || 0 === $quantity) {
                        $booking->removeBookingOption($bookingOption);
                    }
                }

                $price = $calculator->calculateTotalPrice($booking);
//                dd($price);
                $booking->setTotalHTWithoutOptions($priceWO);
                $booking->setTotalHT($price);

//                dd($booking);

                /*
                 * Here, we have a Booking with Options and a final price.
                 * We now need to redirect the customers to the checkout page,
                 * where they will be able to verify their booking and select / add
                 * addresses.
                 * But first, Booking goes to the datatbase, then its id is recovered and set to the session !
                 */

                /*
                 * TODO: Set a specific status for the Booking entity (pending?) and a ttl to automatically remove the booking if it isn't purchased.
                 */

                $em->persist($booking);
                $em->flush();
//                dump($booking);
                $em->refresh($booking);
                $session->set('booking_id', $booking->getId());

                return $this->redirectToRoute('booking_checkout');
            }
        }

        // if we arrive here, something went horribly wrong!
        throw new \LogicException("You shouldn't be here...");
    }

    /**
     * @Route("/checkout", name="booking_checkout")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    public function bookingCheckout(Request $request, SessionInterface $session): Response
    {
        $customer = $this->getUser();

        if ((null === $customer) || ( ! $customer instanceof Customer)) {
            throw new \LogicException('A Customer instance is required.');
        }

        $em = $this->getDoctrine()->getManager();

        $bookingId = $session->get('booking_id');
        /** @var Booking $booking */
        $booking = $this->getDoctrine()->getRepository(Booking::class)->find($bookingId);
        $booking->setCustomer($customer);

        $address = new Address();
        $address->setCustomer($customer);

        $formAddress = $this->createForm(AddressType::class, $address);
        $formAddress->handleRequest($request);

        if ($formAddress->isSubmitted() && $formAddress->isValid()) {
            $customer->addAddress($address);
            $em->persist($address);
            $em->persist($customer);
            $em->flush();
            return $this->redirectToRoute('booking_checkout');
        }

        $order = new Order();
        $order->setBooking($booking);
        $order->setDate(new \DateTime());
        $order->setTotalHT($booking->getTotalHT());

        $formOrder = $this->createForm(OrderType::class, $order);
        $formOrder->handleRequest($request);

        if ($formOrder->isSubmitted() && $formOrder->isValid()) {
//            dd($formOrder);
            /**
             * TODO: checkout the order
             */
        }

        return $this->render('booking/booking_checkout.html.twig', [
            'booking' => $booking,
            'formAddress' => $formAddress->createView(),
            'formOrder' => $formOrder->createView(),
        ]);
    }
}
