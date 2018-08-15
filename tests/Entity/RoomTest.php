<?php

namespace App\Tests\Entity;

use App\Entity\Booking;
use App\Entity\Room;
use App\Exceptions\PriceException;
use PHPUnit\Framework\TestCase;

/**
 * Class RoomTest.
 */
class RoomTest extends TestCase
{
    /**
     * Test to Instanciate Entity.
     */
    public function testRoomCanBeCreated(): void
    {
        $room = new Room();
        $this->assertInstanceOf(Room::class, $room);
    }

    /**
     * Test Monthly price can't be null.
     */
    public function testMonthlyPriceIsBiggerThanZero(): void
    {
        $room = new Room();
        $this->expectException(PriceException::class);

        $room->setMonthlyPrice(0);
    }

    /**
     * Test Weekly price can't be null.
     */
    public function testWeeklyPriceIsBiggerThanZero(): void
    {
        $room = new Room();
        $this->expectException(PriceException::class);

        $room->setWeeklyPrice(0);
    }

    /**
     * Test Daily price can't be null.
     */
    public function testDailyPriceIsBiggerThanZero(): void
    {
        $room = new Room();
        $this->expectException(PriceException::class);

        $room->setDailyPrice(0);
    }

    /**
     * Test Hourly price can't be null.
     */
    public function testHourlyPriceIsBiggerThanZero(): void
    {
        $room = new Room();
        $this->expectException(PriceException::class);

        $room->setHourlyPrice(0);
    }

    /**
     * Try to add a booking to a room.
     *
     * @return Room
     */
    public function testInsertBooking(): Room
    {
        $room = new Room();
        $booking = new Booking();
        $booking2 = new Booking();

        $room->addBooking($booking);
        $this->assertCount(1, $room->getBookings());

        $room->addBooking($booking2);
        $this->assertCount(2, $room->getBookings());

        return $room;
    }

    /**
     * @depends testInsertBooking
     *
     * @param Room $room
     */
    public function testRemoveBooking(Room $room): void
    {
        $bookings = $room->getBookings();
        $room->removeBooking($bookings[0]);
        $this->assertCount(1, $room->getBookings());

        $room->removeBooking($bookings[1]);
        $this->assertCount(0, $room->getBookings());
    }
}
