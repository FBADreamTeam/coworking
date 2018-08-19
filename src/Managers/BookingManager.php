<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 14/08/2018
 * Time: 16:35.
 */

namespace App\Managers;

use App\Entity\Booking;
use App\Entity\BookingOptions;
use App\Entity\Customer;
use App\Entity\Room;
use App\Exceptions\BookingInvalidDatesException;
use App\Exceptions\PriceException;
use App\Services\BookingPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BookingManager extends AbstractManager
{
    /**
     * @var BookingPriceCalculator
     */
    protected $calculator;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * BookingManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param BookingPriceCalculator $calculator
     * @param SerializerInterface    $serializer
     */
    public function __construct(EntityManagerInterface $em, BookingPriceCalculator $calculator, SerializerInterface $serializer)
    {
        parent::__construct($em);
        $this->calculator = $calculator;
        $this->serializer = $serializer;
    }

    /**
     * @param Booking  $booking
     * @param Customer $customer
     *
     * @return bool
     */
    public static function checkBookingCustomerIsValid(Booking $booking, Customer $customer): bool
    {
        return $booking->getCustomer() === $customer;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @throws BookingInvalidDatesException
     */
    public static function checkDateBeforeNow(\DateTimeInterface $date): void
    {
        if ($date <= new \DateTime()) {
            throw new BookingInvalidDatesException(BookingInvalidDatesException::DATES_BEFORE_NOW);
        }
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     *
     * @throws BookingInvalidDatesException
     */
    public static function checkDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        if ($startDate > $endDate) {
            throw new BookingInvalidDatesException();
        }
    }

    /**
     * @param int $price
     *
     * @throws PriceException
     */
    public static function checkPrice(int $price): void
    {
        if ($price <= 0) {
            throw new PriceException();
        }
    }

    /**
     * @param int    $roomId
     * @param string $startDate
     * @param string $endDate
     *
     * @return Booking
     *
     * @throws \Exception
     */
    public function createBookingFromRoomAndDates(int $roomId, string $startDate, string $endDate): Booking
    {
        $booking = new Booking();

        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        // check dates
        self::checkDateBeforeNow($startDateTime);
        self::checkDateBeforeNow($endDateTime);
        self::checkDates($startDateTime, $endDateTime);

        // finding the room
        /** @var Room $room */
        $room = $this->em->getRepository(Room::class)->find($roomId);
        $booking->setRoom($room);

        $booking->setStartDate($startDateTime);
        $booking->setEndDate($endDateTime);

        // getting the roomOptions
//        if (null === $room->getRoomType()) {
//            throw new \LogicException('Room instance should have a RoomType at this point.');
//        }
//        $roomOptions = $room->getRoomType()->getRoomOptions();
//
//        // creating bookingOptions
//        foreach ($roomOptions as $roomOption) {
//            $bookingOption = new BookingOptions();
//            $bookingOption->setRoomOption($roomOption);
//            $booking->addBookingOption($bookingOption);
//        }

        return $booking;
    }

    /**
     * @param Booking $booking
     */
    public function createBookingOptions(Booking $booking): void
    {
        $room = $booking->getRoom();

        // getting the roomOptions
        if (null === $room->getRoomType()) {
            throw new \LogicException('Room instance should have a RoomType at this point.');
        }
        $roomOptions = $room->getRoomType()->getRoomOptions();

        // creating bookingOptions
        foreach ($roomOptions as $roomOption) {
            $bookingOption = new BookingOptions();
            $bookingOption->setRoomOption($roomOption);
            $booking->addBookingOption($bookingOption);
        }
    }

    /**
     * @param Booking $booking
     *
     * @throws \Exception
     */
    public function calculatePriceWithoutOptions(Booking $booking): void
    {
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        self::checkPrice($price);
        $booking->setTotalHTWithoutOptions($price);
    }

    public function clearEmptyOptions(Booking $booking): void
    {
        // check all the options
        foreach ($booking->getBookingOptions() as $bookingOption) {
            // if quantity is null || 0, we remove the option
            $quantity = $bookingOption->getQuantity();
            if (null === $quantity || 0 === $quantity) {
                $booking->removeBookingOption($bookingOption);
            }
        }
    }

    /**
     * @param Booking $booking
     *
     * @throws \Exception
     */
    public function calculateHTPrice(Booking $booking): void
    {
        // first, we clear the options with 0 or null quantity
        $this->clearEmptyOptions($booking);
        // then, we calculate the price
        $price = $this->calculator->calculateTotalPrice($booking);
        self::checkPrice($price);
        $booking->setTotalHT($price);
    }

    /**
     * @param int    $roomId
     * @param string $startDate
     * @param string $endDate
     *
     * @return Booking
     *
     * @throws \Exception
     */
    public function createBookingAndCalculatePriceWithoutOptions(int $roomId, string $startDate, string $endDate): Booking
    {
        $booking = $this->createBookingFromRoomAndDates(
            $roomId,
            $startDate,
            $endDate
        );

        $this->createBookingOptions($booking);
        $this->calculatePriceWithoutOptions($booking);

        return $booking;
    }

    /**
     * @param Booking $booking
     *
     * @throws \Exception
     */
    public function handleBookingOptionsRequest(Booking $booking): void
    {
        $this->calculateHTPrice($booking);
        /*
         * TODO: Set a specific status for the Booking entity (pending?) and a ttl to automatically remove the booking if it isn't purchased.
         */
    }

    /**
     * @param Booking $booking
     */
    public function handleCreateRequest(Booking $booking): void
    {
        $this->em->persist($booking);
        $this->em->flush();
    }
}
