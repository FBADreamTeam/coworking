<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 18/08/2018
 * Time: 18:17.
 */

namespace App\Tests\Entity;

use App\Entity\Booking;
use App\Entity\BookingOptions;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Room;
use App\Entity\RoomOption;
use App\Exceptions\BookingInvalidDatesException;
use App\Exceptions\PriceException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase
{
    /** @var Booking */
    protected $booking;

    protected function setUp()
    {
        $this->booking = new Booking();
    }

    public function testCanBeConstructed(): void
    {
        $this->assertInstanceOf(Booking::class, $this->booking);
    }

    public function testIdCanBeGotten(): void
    {
        $setIdClosure = function () {
            $this->id = 1;
        };
        $doSetIdClosure = $setIdClosure->bindTo($this->booking, Booking::class);
        $doSetIdClosure();
        $this->assertEquals(1, $this->booking->getId());
    }

    public function testCustomerCanBeSetAndGotten(): void
    {
        $customer = new Customer();
        $customer->setEmail('test@test.xyz');
        $this->booking->setCustomer($customer);
        $this->assertSame($customer, $this->booking->getCustomer());
    }

    public function testTotalHTCanBeSetAndGotten(): void
    {
        $this->booking->setTotalHT(1000);
        $this->assertEquals(1000, $this->booking->getTotalHT());
    }

    public function testRoomCanBeSetAndGotten(): void
    {
        $room = new Room();
        $this->booking->setRoom($room);
        $this->assertSame($room, $this->booking->getRoom());
    }

    public function testTotalHTWithoutOptionsCanBeSetAndGotten(): void
    {
        $this->booking->setTotalHTWithoutOptions(1000);
        $this->assertEquals(1000, $this->booking->getTotalHTWithoutOptions());
    }

    public function testEndDateCanBeSetAndGotten(): void
    {
        $date = new \DateTime();
        $this->booking->setEndDate($date);
        $this->assertSame($date, $this->booking->getEndDate());
    }

    public function testStartDateCanBeSetAndGotten(): void
    {
        $date = new \DateTime();
        $this->booking->setStartDate($date);
        $this->assertSame($date, $this->booking->getStartDate());
    }

    public function testBookingOptionCanBeRemoved(): void
    {
        $bookingOption = new BookingOptions();
        $this->booking->addBookingOption($bookingOption);
        $this->booking->removeBookingOption($bookingOption);
        $this->assertEmpty($this->booking->getBookingOptions());
    }

    public function testOrderCanBeSetAndGotten(): void
    {
        $order = new Order();
        $this->booking->setOrder($order);
        $this->assertSame($order, $this->booking->getOrder());
    }

    public function testGetRoomOptionsAsHashedArray(): void
    {
        /** @var MockObject&RoomOption $roomOption */
        $roomOption = $this->createMock(RoomOption::class);
        $roomOption->method('getId')->willReturn(1);
        $bookingOption = new BookingOptions();
        $bookingOption->setRoomOption($roomOption);
        $this->booking->addBookingOption($bookingOption);
        $this->assertCount(1, $this->booking->getRoomOptionsAsHashedArray());
        $this->assertArrayHasKey(1, $this->booking->getRoomOptionsAsHashedArray());
        $this->assertSame($roomOption, $this->booking->getRoomOptionsAsHashedArray()[1]);
    }

    public function testBookingOptionCanBeAdded(): void
    {
        $bookingOption = new BookingOptions();
        $this->booking->addBookingOption($bookingOption);
        $this->assertCount(1, $this->booking->getBookingOptions());
    }

    public function testBookingOptionsCanBeGotten(): void
    {
        $bookingOption = new BookingOptions();
        $this->booking->addBookingOption($bookingOption);
        $this->assertCount(1, $this->booking->getBookingOptions());
        $this->assertInstanceOf(ArrayCollection::class, $this->booking->getBookingOptions());
    }

    public function testStartDateCannotBeAfterEndDate(): void
    {
        $this->expectException(BookingInvalidDatesException::class);
        $this->booking->setEndDate(new \DateTime('2018-08-18'));
        $this->booking->setStartDate(new \DateTime('2018-08-19'));
    }

    public function testBookingPricesCannotBeLessOrEqualToZero(): void
    {
        $this->expectException(PriceException::class);
        $this->booking->setTotalHT(0);
        $this->expectException(PriceException::class);
        $this->booking->setTotalHTWithoutOptions(0);
        $this->expectException(PriceException::class);
        $this->booking->setTotalHT(-10);
        $this->expectException(PriceException::class);
        $this->booking->setTotalHTWithoutOptions(-10);
    }
}
