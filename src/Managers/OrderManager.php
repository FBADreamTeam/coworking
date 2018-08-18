<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 16/08/2018
 * Time: 12:03.
 */

namespace App\Managers;

use App\Entity\Booking;
use App\Entity\Order;
use App\Entity\Room;
use App\Entity\RoomOption;
use App\Events\OrderEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderManager extends AbstractManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * OrderManager constructor.
     *
     * @param EntityManagerInterface   $em
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($em);
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Booking $booking
     *
     * @return Order
     */
    public function createOrderFromBooking(Booking $booking): Order
    {
        $order = new Order();
        $order->setBooking($booking);
        $order->setDate(new \DateTime());
        $order->setTotalHT($booking->getTotalHT());

        return $order;
    }

    /**
     * @param Order $order
     */
    public function handleCreateRequest(Order $order): void
    {
        $booking = $order->getBooking();
        $this->refreshBookingFromSession($booking);
        $this->em->persist($booking);
        $this->em->persist($order);
        $this->em->flush();

        $orderEvent = new OrderEvents($order);
        $this->dispatcher->dispatch(OrderEvents::ORDER_PLACED, $orderEvent);
    }

    /**
     * @param Booking $booking
     */
    private function refreshBookingFromSession(Booking $booking): void
    {
        // refresh room
        $room = $booking->getRoom();
        /** @var Room $roomFromDB */
        $roomFromDB = $this->em->getRepository(Room::class)->find($room->getId());
        $this->em->refresh($roomFromDB);
        $booking->setRoom($roomFromDB);

        // refresh roomOptions
        $bookingOptions = $booking->getBookingOptions();
        $roomOptionsRepo = $this->em->getRepository(RoomOption::class);
        foreach ($bookingOptions as $bookingOption) {
            /** @var RoomOption $roomOption */
            $roomOption = $roomOptionsRepo->find($bookingOption->getRoomOption()->getId());
            $this->em->refresh($roomOption);
            $bookingOption->setRoomOption($roomOption);
        }
    }
}
