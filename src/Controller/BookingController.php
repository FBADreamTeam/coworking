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
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Room;
use App\Entity\RoomType;
use App\Events\OrderEvents;
use App\Form\AddressType;
use App\Form\BookingAddOptionsType;
use App\Form\OrderType;
use App\Managers\AddressManager;
use App\Managers\BookingManager;
use App\Managers\OrderManager;
use App\Managers\RoomManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     *
     * @param SessionInterface $session
     *
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
            $data = [];
            // get all types of rooms
            $types = $this->getDoctrine()->getRepository(RoomType::class)->findAll();
            // Filter rooms by availability
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
     * @param Request             $request
     * @param SessionInterface    $session
     * @param BookingManager      $bookingManager
     * @param SerializerInterface $serializer
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse|Response
     *
     * @throws \Exception
     */
    public function selectOptions(Request $request, SessionInterface $session, BookingManager $bookingManager, SerializerInterface $serializer, TranslatorInterface $translator)
    {
        // Request comes from calendar view, expects an url
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            // set the room and date values in session
            $this->setSessionKeys($session, $request->get('roomId'), $request->get('startDate'), $request->get('endDate'));

            // Return url to form
            return new JsonResponse(['url' => $this->generateUrl('booking_options')]);
        }

        // Request comes from redirecting from ajax, expects a form
        if (!$request->isXmlHttpRequest() && $request->isMethod('GET')) {
            // Create a Booking from a room and dates
            $booking = $bookingManager->createBookingAndCalculatePriceWithoutOptions(
                (int) $session->get('roomId'),
                $session->get('startDate'),
                $session->get('endDate')
            );
            // Get the start and end dates from the session as DateTime instances
            $startDateTime = new \DateTime($session->get('startDate'));
            $endDateTime = new \DateTime($session->get('endDate'));
            // Create the form
            $form = $this->createForm(BookingAddOptionsType::class, $booking);
            // Render the template
            return $this->render('booking/options_form.html.twig', [
                'interval' => $startDateTime->diff($endDateTime),
                'room' => $booking->getRoom(),
                'booking' => $booking,
                'encodedOptions' => $serializer->serialize($booking->getRoomOptionsAsHashedArray(), 'json', ['groups' => 'options']),
                'form' => $form->createView(),
            ]);
        }

        // Request comes from POST, payload is a form with booking options
        if (!$request->isXmlHttpRequest() && $request->isMethod('POST')) {
            // Create a Booking from a room and dates
            $booking = $bookingManager->createBookingAndCalculatePriceWithoutOptions(
                (int) $session->get('roomId'),
                $session->get('startDate'),
                $session->get('endDate')
            );
            // Create the form and handle the request
            $form = $this->createForm(BookingAddOptionsType::class, $booking)
                ->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle the request and calculate the price
                $bookingManager->handleCreateRequest($booking);
                // Set the booking id in the session for further use
                $session->set('booking_id', $booking->getId());
                // If customer is not already logged in, add a message flash before the Security redirection
                if (null === $this->getUser()) {
                    $this->addFlash('notice', $translator->trans('booking.msg.customermustlog', [], 'booking'));
                }
                // Redirect to checkout
                return $this->redirectToRoute('booking_checkout');
            }
        }

        // if we get to here, something went horribly wrong!
        throw new \LogicException('A system error occurred. Our team has been informed.');
    }

    /**
     * @Route("/checkout", name="booking_checkout")
     *
     * @param Request             $request
     * @param SessionInterface    $session
     * @param BookingManager      $bookingManager
     * @param AddressManager      $addressManager
     * @param OrderManager        $orderManager
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function bookingCheckout(Request $request, SessionInterface $session, BookingManager $bookingManager, AddressManager $addressManager, OrderManager $orderManager, TranslatorInterface $translator): Response
    {
        // Get the customer
        /** @var Customer $customer */
        $customer = $this->getUser();

        // Try to get a booking from the id in the session and add to it the current customer
        try {
            $booking = $bookingManager->getBookingFromIdWithCustomer(
                (int) $session->get('booking_id'),
                $customer
            );
        } catch (\Exception $e) {
            $this->addFlash('notice', $translator->trans('booking.error.missingId', [], 'booking'));

            return $this->redirectToRoute('booking_index');
        }

        // Create a new address and attach the current customer to it
        $address = $addressManager->getNewAddressWithCustomer($customer);
        // Create the form with the new address and handle the request
        $formAddress = $this->createForm(AddressType::class, $address)
            ->handleRequest($request);

        if ($formAddress->isSubmitted() && $formAddress->isValid()) {
            // Handle the request, persist the new address and add it to the current customer
            $addressManager->handleCreateRequest($address);
            // Redirect to the current checkout
            return $this->redirectToRoute('booking_checkout');
        }

        // Create a new order from the booking
        $order = $orderManager->createOrderFromBooking($booking);
        // Create the form with the new order and handle the request
        $formOrder = $this->createForm(OrderType::class, $order)
            ->handleRequest($request);

        if ($formOrder->isSubmitted() && $formOrder->isValid()) {
            // Handle the request, persist the order and dispatch an OrderEvents::ORDER_PLACED event
            $orderManager->handleCreateRequest($order);
            // Redirect to the confirmation view
            return $this->redirectToRoute('booking_confirm', ['id' => $booking->getId()]);
        }
        // Render the checkout view
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
     *
     * @return Response
     */
    public function bookingConfirm(Booking $booking): Response
    {
        // Check if the customer in the booking is the same as the one logged in
        if (!BookingManager::checkBookingCustomerIsValid($booking, $this->getUser())) {
            $this->addFlash('error', 'customer.invalid');

            return $this->redirectToRoute('index');
        }
        // Render the confirmation view
        return $this->render('booking/booking_confirm.html.twig', [
            'booking' => $booking,
        ]);
    }

    /**
     * Removes all the session keys associated with a booking.
     *
     * @param SessionInterface $session
     */
    private function cleanSession(SessionInterface $session): void
    {
        $session->remove('roomId');
        $session->remove('startDate');
        $session->remove('endDate');
        $session->remove('booking_id');
    }

    /**
     * Sets the session keys/values associated with a booking.
     *
     * @param SessionInterface $session
     * @param int|null         $roomId
     * @param string|null      $startDate
     * @param string|null      $endDate
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
