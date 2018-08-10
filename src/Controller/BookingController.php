<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 31/07/2018
 * Time: 14:50.
 */

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\BookingOptions;
use App\Entity\Customer;
use App\Entity\Room;
use App\Entity\RoomType;
use App\Form\BookingAddOptionsType;
use App\Managers\RoomManager;
use App\Services\BookingPriceCalculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
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
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
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
//            dump($booking->getStartDate());
//            dump($booking->getEndDate());
//            dump($booking->getRoom());
//            dump($price);

            if ($form->isSubmitted() && $form->isValid()) {
//                dump($booking);
                // check all the options
                foreach ($booking->getBookingOptions() as $bookingOption) {
                    // if quantity is null || 0, we remove the option
                    $quantity = $bookingOption->getQuantity();
                    if (null === $quantity || 0 === $quantity) {
                        $booking->removeBookingOption($bookingOption);
                    }
                }
//                dd($booking);

                $price = $calculator->calculateTotalPrice($booking);
//                dd($price);
                $booking->setTotalHTWithoutOptions($priceWO);
                $booking->setTotalHT($price);

                dd($booking);

                // before flushing, for testing purpose, we attach a fixed customer
                /** @var Customer $customer */
                $customer = $em->getRepository(Customer::class)->findAll()[0];
                $booking->setCustomer($customer);

                $em->persist($booking);
                $em->flush();

                /*
                 * TODO: now, we have to check if the user is logged in or not. If not, we redirect the user to a login page, else we continue to the recap page before payment
                 */

                $this->addFlash('notice', $translator->trans('booking.msg.success', [], 'booking'));

                return $this->redirectToRoute('index');
            }
        }

        // if we arrive here, something went horribly wrong!
        throw new \LogicException("You shouldn't be here...");
    }
}
