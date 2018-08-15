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
use App\Events\OrderEvents;
use App\Form\AddressType;
use App\Form\BookingAddOptionsType;
use App\Form\OrderType;
use App\Managers\BookingManager;
use App\Managers\RoomManager;
use App\Services\BookingPriceCalculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @param SessionInterface $session
     * @return Response
     */
    public function index(SessionInterface $session): Response
    {
        // delete booking keys in session
        $this->cleanSession($session);

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
     * @Route("/filter", name="booking_filter", methods={"GET"})
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
            $data = [];

            // get all types of rooms
            $types = $this->getDoctrine()->getRepository(RoomType::class)->findAll();

            /** @var RoomType $type */
            foreach ($types as $type) {
                $data[$type->getId()] = $roomManager->filterByType($type, $request->get('startDate'), $request->get('endDate'));
            }

            return new JsonResponse(['rooms' => $roomManager->serialize($data)]);
        }

        $this->addFlash('notice', 'Sorry, unauthorized action');
        return $this->redirectToRoute('booking_index');
    }

    /**
     * @Route("/options", name="booking_options")
     *
     * @param Request $request
     * @param SessionInterface $session
     *
     * @param BookingManager $bookingManager
     * @param SerializerInterface $serializer
     * @param TranslatorInterface $translator
     * @return JsonResponse|Response
     * @throws \Exception
     */
    public function selectOptions(Request $request, SessionInterface $session, BookingManager $bookingManager, SerializerInterface $serializer, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        // request comes from calendar view, expects an url
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            // set the room and date values in session
            $this->setSessionKeys($session, $request->get('roomId'), $request->get('startDate'), $request->get('endDate'));

            // return url to form
            return new JsonResponse(['url' => $this->generateUrl('booking_options')]);
        }

        // request comes from redirecting from ajax, expects a form
        if (! $request->isXmlHttpRequest() && $request->isMethod('GET')) {
            // create a Booking from a room and dates
            $booking = $bookingManager->createBookingFromRoomAndDates(
                (int)$session->get('roomId'),
                $session->get('startDate'),
                $session->get('endDate')
            );
            // set the available options from the room
            $bookingManager->createBookingOptions($booking);
            // calculate the basic price from the selected dates of the booking
            $bookingManager->calculatePriceWithoutOptions($booking);

            $startDateTime = new \DateTime($session->get('startDate'));
            $endDateTime = new \DateTime($session->get('endDate'));

            $form = $this->createForm(BookingAddOptionsType::class, $booking);

            return $this->render('booking/options_form.html.twig', [
                'interval'          => $startDateTime->diff($endDateTime),
                'room'              => $booking->getRoom(),
                'booking'           => $booking,
                'encodedOptions'    => $serializer->serialize($booking->getRoomOptionsAsHashedArray(), 'json', ['groups' => 'options']),
                'form'              => $form->createView(),
            ]);
        }

        // request comes from POST, payload is a form with booking options
        if (!$request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $booking = $bookingManager->createBookingAndCalculatePriceWithoutOptions(
                (int)$session->get('roomId'),
                $session->get('startDate'),
                $session->get('endDate')
            );

            $form = $this->createForm(BookingAddOptionsType::class, $booking)
                ->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $bookingManager->calculateHTPrice($booking);

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
                $em->refresh($booking);
                $session->set('booking_id', $booking->getId());

                if (null === $this->getUser()) {
                    $this->addFlash('notice', $translator->trans('booking.msg.customermustlog', [], 'booking'));
                }

                return $this->redirectToRoute('booking_checkout');
            }
        }

        // if we get to here, something went horribly wrong!
        throw new \LogicException('A system error occurred. Our team has been informed.');
    }

    /**
     * @Route("/checkout", name="booking_checkout")
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param EventDispatcherInterface $dispatcher
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function bookingCheckout(Request $request, SessionInterface $session, EventDispatcherInterface $dispatcher, TranslatorInterface $translator): Response
    {
        $customer = $this->getUser();

        if ((null === $customer) || (! $customer instanceof Customer)) {
            throw new \LogicException('A Customer instance is required.');
        }

        $bookingId = $session->get('booking_id');

        if (null === $bookingId) {
            $this->addFlash('notice', $translator->trans('booking.error.missingId', [], 'booking'));
            return $this->redirectToRoute('booking_index');
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Booking $booking */
        $booking = $em->getRepository(Booking::class)->find($bookingId);
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
            $em->persist($booking);
            $em->persist($order);
            $em->flush();

            $orderEvent = new OrderEvents($order);
            $dispatcher->dispatch(OrderEvents::ORDER_PLACED, $orderEvent);

            return $this->redirectToRoute('booking_confirm', ['id' => $booking->getId()]);
        }

        return $this->render('booking/booking_checkout.html.twig', [
            'booking' => $booking,
            'formAddress' => $formAddress->createView(),
            'formOrder' => $formOrder->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{id}", name="booking_confirm")
     *
     * @IsGranted("ROLE_USER")
     *
     * @param Booking $booking
     * @return Response
     */
    public function bookingConfirm(Booking $booking): Response
    {
        if (! BookingManager::checkBookingCustomerIsValid($booking, $this->getUser())) {
            $this->addFlash('error', 'customer.invalid');
            return $this->redirectToRoute('index');
        }

        return $this->render('booking/booking_confirm.html.twig', [
            'booking' => $booking,
        ]);
    }

    /**
     * @param SessionInterface $session
     * @return void
     */
    private function cleanSession(SessionInterface $session): void
    {
        $session->remove('roomId');
        $session->remove('startDate');
        $session->remove('endDate');
        $session->remove('booking_id');
    }

    /**
     * @param SessionInterface $session
     * @param int|null $roomId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return void
     */
    private function setSessionKeys(
        SessionInterface $session,
        int $roomId = null,
        string $startDate = null,
        string $endDate = null
    ): void {
        // set the room and date values in session
        $session->set('roomId', $roomId);
        $session->set('startDate', $startDate);
        $session->set('endDate', $endDate);
    }
}
