<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 19/08/2018
 * Time: 13:25.
 */

namespace App\Tests\Services;

use App\Entity\Booking;
use App\Entity\BookingOptions;
use App\Entity\Room;
use App\Services\BookingPriceCalculator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class BookingPriceCalculatorTest extends TestCase
{
    /** @var string */
    protected $businessHourStart;

    /** @var string */
    protected $businessHourEnd;

    /** @var BookingPriceCalculator */
    protected $calculator;

    public function setUp(): void
    {
        $this->businessHourStart = '09:00:00';
        $this->businessHourEnd = '19:00:00';
        $this->calculator = new BookingPriceCalculator($this->businessHourStart, $this->businessHourEnd);
    }

    public function testCanBeConstructed(): void
    {
        $this->assertInstanceOf(BookingPriceCalculator::class, $this->calculator);
    }

    public function testCalculateTotalPrice(): void
    {
//        $days = $this->getBusinessDaysCount($booking->getStartDate(), $booking->getEndDate());
//        $price = $this->calculateTotalPriceWithoutOptions($booking);
//        $options = $booking->getBookingOptions();
//        /** @var BookingOptions $option */
//        foreach ($options as $option) {
//            $optionTotal = $option->getPrice() * $option->getQuantity() * $days;
//            $option->setTotal($optionTotal);
//            $price += $option->getTotal();
//        }
//
//        return $price;

        $booking = new Booking();
        /** @var Room&PHPUnit_Framework_MockObject_MockObject $roomMock */
        $roomMock = $this->getRoomMock();
        $booking->setRoom($roomMock);

        /** @var BookingOptions&PHPUnit_Framework_MockObject_MockObject $bookingOptionMock1 */
        $bookingOptionMock1 = $this->getMockBuilder(BookingOptions::class)
            ->setMethods(['getPrice', 'getQuantity'])
            ->getMock()
        ;
        $bookingOptionMock1
            ->method('getPrice')
            ->willReturn(1000)
        ;
        $bookingOptionMock1
            ->method('getQuantity')
            ->willReturn(2)
        ;

        /** @var BookingOptions&PHPUnit_Framework_MockObject_MockObject $bookingOptionMock2 */
        $bookingOptionMock2 = $this->getMockBuilder(BookingOptions::class)
            ->setMethods(['getPrice', 'getQuantity'])
            ->getMock()
        ;
        $bookingOptionMock2
            ->method('getPrice')
            ->willReturn(2000)
        ;
        $bookingOptionMock2
            ->method('getQuantity')
            ->willReturn(3)
        ;

        // 1 day = 10000
        $booking->setStartDate(new \DateTime('2018-08-06 09:00:00'));
        $booking->setEndDate(new \DateTime('2018-08-06 19:00:00'));

        $booking->addBookingOption($bookingOptionMock1)->addBookingOption($bookingOptionMock2);
        $price = $this->calculator->calculateTotalPrice($booking);
        $this->assertEquals(18000, $price);
    }

    public function testCalculateTotalPriceWithoutOptions(): void
    {
        $booking = new Booking();
        $this->assertNull($this->calculator->calculateTotalPriceWithoutOptions($booking));

        /** @var Room&PHPUnit_Framework_MockObject_MockObject $roomMock */
        $roomMock = $this->getRoomMock();
        $booking->setRoom($roomMock);

        // test month + days
        $booking->setStartDate(new \DateTime('2018-08-01'));
        $booking->setEndDate(new \DateTime('2018-08-31'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(140000, $price);

        // test month (20 days)
        $booking->setStartDate(new \DateTime('2018-08-01'));
        $booking->setEndDate(new \DateTime('2018-08-29'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(120000, $price);

        // test 2 weeks
        $booking->setStartDate(new \DateTime('2018-08-06'));
        $booking->setEndDate(new \DateTime('2018-08-17'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(80000, $price);

        // test week
        $booking->setStartDate(new \DateTime('2018-08-06'));
        $booking->setEndDate(new \DateTime('2018-08-10'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(40000, $price);

        // test 6 days
        $booking->setStartDate(new \DateTime('2018-08-06 09:00:00'));
        $booking->setEndDate(new \DateTime('2018-08-13 19:00:00'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(50000, $price);

        // test day
        $booking->setStartDate(new \DateTime('2018-08-06 09:00:00'));
        $booking->setEndDate(new \DateTime('2018-08-06 19:00:00'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(10000, $price);

        // test hours
        $booking->setStartDate(new \DateTime('2018-08-06 09:00:00'));
        $booking->setEndDate(new \DateTime('2018-08-06 14:00:00'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(5000, $price);

        // test hour
        $booking->setStartDate(new \DateTime('2018-08-06 09:00:00'));
        $booking->setEndDate(new \DateTime('2018-08-06 10:00:00'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(1000, $price);

        // test 00:00:00 - 1 day
        $booking->setStartDate(new \DateTime('2018-08-06 00:00:00'));
        $booking->setEndDate(new \DateTime('2018-08-07 00:00:00'));
        $price = $this->calculator->calculateTotalPriceWithoutOptions($booking);
        $this->assertEquals(10000, $price);
    }

    private function getRoomMock()
    {
        /** @var Room&PHPUnit_Framework_MockObject_MockObject $roomMock */
        $roomMock = $this->getMockBuilder(Room::class)
            ->setMethods(['getMonthlyPrice', 'getWeeklyPrice', 'getDailyPrice', 'getHourlyPrice'])
            ->getMock()
        ;
        $roomMock->method('getMonthlyPrice')->willReturn(120000);
        $roomMock->method('getWeeklyPrice')->willReturn(40000);
        $roomMock->method('getDailyPrice')->willReturn(10000);
        $roomMock->method('getHourlyPrice')->willReturn(1000);

        return $roomMock;
    }
}
