<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 18/08/2018
 * Time: 21:55.
 */

namespace App\Tests\Managers;

use App\Entity\Booking;
use App\Entity\BookingOptions;
use App\Entity\Customer;
use App\Entity\Room;
use App\Entity\RoomOption;
use App\Entity\RoomType;
use App\Exceptions\BookingInvalidDatesException;
use App\Exceptions\PriceException;
use App\Managers\BookingManager;
use App\Repository\RoomRepository;
use App\Services\BookingPriceCalculator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use PHPUnit_Framework_MockObject_MockObject;

class BookingManagerTest extends TestCase
{
    /** @var BookingManager */
    protected $bookingManager;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $emMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $calculatorMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $serializerMock;

    /** @var \DateTime */
    protected $startDate;

    /** @var \DateTime */
    protected $endDate;

    protected function setUp(): void
    {
        /** @var EntityManagerInterface&PHPUnit_Framework_MockObject_MockObject $emMock */
        $emMock = $this->createMock(EntityManagerInterface::class);
        /** @var BookingPriceCalculator&PHPUnit_Framework_MockObject_MockObject $calculatorMock */
        $calculatorMock = $this->createMock(BookingPriceCalculator::class);
        /** @var SerializerInterface&PHPUnit_Framework_MockObject_MockObject $serializerMock */
        $serializerMock = $this->createMock(SerializerInterface::class);

        $this->bookingManager = new BookingManager($emMock, $calculatorMock, $serializerMock);
        // have to fool PHPStan...
        $this->emMock = $emMock;
        $this->calculatorMock = $calculatorMock;
        $this->serializerMock = $serializerMock;

        $this->startDate = new \DateTime();
        $this->startDate->add(new \DateInterval('P2D'));

        $this->endDate = new \DateTime();
        $this->endDate->add(new \DateInterval('P3D'));
    }

    public function testCanBeConstructed(): void
    {
        $this->assertInstanceOf(BookingManager::class, $this->bookingManager);
    }

    public function testCheckBookingCustomerIsValid(): void
    {
        $customer1 = new Customer();
        $customer2 = new Customer();
        $booking = new Booking();
        $booking->setCustomer($customer1);
        $this->assertTrue(BookingManager::checkBookingCustomerIsValid($booking, $customer1));
        $this->assertFalse(BookingManager::checkBookingCustomerIsValid($booking, $customer2));
    }

    public function testCreateBookingFromRoomAndDates(): void
    {
        $roomMock = $this->getMockBuilder(Room::class)->setMethods(['getId'])->getMock();
        $roomMock->method('getId')->willReturn(1);

        $roomRepoMock = $this->getMockBuilder(RoomRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])->getMock();
        $roomRepoMock->method('find')->willReturn($roomMock);

        $this->emMock->method('getRepository')->willReturn($roomRepoMock);
        $this->emMock->expects($this->exactly(1))->method('getRepository')->with($this->equalTo(Room::class));

        $booking = $this->bookingManager->createBookingFromRoomAndDates(1, $this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d'));

        $this->assertEquals(1, $booking->getRoom()->getId());
        $this->assertInstanceOf(\DateTimeInterface::class, $booking->getStartDate());
        $this->assertEquals($this->startDate->format('Y-m-d'), $booking->getStartDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTimeInterface::class, $booking->getEndDate());
        $this->assertEquals($this->endDate->format('Y-m-d'), $booking->getEndDate()->format('Y-m-d'));

        $this->expectException(BookingInvalidDatesException::class);
        $this->bookingManager->createBookingFromRoomAndDates(1, $this->endDate->format('Y-m-d'), $this->startDate->format('Y-m-d'));
    }

    public function testCheckDateBeforeNow(): void
    {
        $startDate = (new \DateTime())->sub(new \DateInterval('P4D'));
        $endDate = (new \DateTime())->sub(new \DateInterval('P5D'));

        $this->expectException(BookingInvalidDatesException::class);
        $this->expectExceptionMessage(BookingInvalidDatesException::DATES_BEFORE_NOW);
        $this->bookingManager->createBookingFromRoomAndDates(1, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
    }

    public function testClearEmptyOptions(): void
    {
        $booking = new Booking();

        // test quantities at 0
        for ($i = 0; $i < 4; ++$i) {
            $bookingOption = new BookingOptions();
            $bookingOption->setQuantity($i % 2);
            $booking->addBookingOption($bookingOption);
        }

        // test quantity at null
        $bookingOption = new BookingOptions();
        $bookingOption->setQuantity(null);
        $booking->addBookingOption($bookingOption);

        $this->bookingManager->clearEmptyOptions($booking);
        $this->assertCount(2, $booking->getBookingOptions());
    }

    public function testCalculatePriceWithoutOptions(): void
    {
        $this->calculatorMock
            ->method('calculateTotalPriceWithoutOptions')
            ->willReturn(1000)
        ;
        $this->calculatorMock->expects($this->any())
            ->method('calculateTotalPriceWithoutOptions')
            ->with($this->isInstanceOf(Booking::class))
        ;

        $booking = new Booking();
        $this->bookingManager->calculatePriceWithoutOptions($booking);

        $this->assertEquals(1000, $booking->getTotalHTWithoutOptions());

        // test price checks
        /** @var EntityManagerInterface&PHPUnit_Framework_MockObject_MockObject $emMock */
        $emMock = $this->createMock(EntityManagerInterface::class);
        /** @var SerializerInterface&PHPUnit_Framework_MockObject_MockObject $serializerMock */
        $serializerMock = $this->createMock(SerializerInterface::class);
        /** @var BookingPriceCalculator&PHPUnit_Framework_MockObject_MockObject $calculatorMock */
        $calculatorMock = $this->createMock(BookingPriceCalculator::class);
        $calculatorMock
            ->method('calculateTotalPriceWithoutOptions')
            ->willReturn(0)
        ;

        $bookingManager = new BookingManager($emMock, $calculatorMock, $serializerMock);
        $this->expectException(PriceException::class);
        $bookingManager->calculatePriceWithoutOptions($booking);

        /** @var BookingPriceCalculator&PHPUnit_Framework_MockObject_MockObject $calculatorMock */
        $calculatorMock = $this->createMock(BookingPriceCalculator::class);
        $calculatorMock
            ->method('calculateTotalPriceWithoutOptions')
            ->willReturn(-10)
        ;

        $bookingManager = new BookingManager($emMock, $calculatorMock, $serializerMock);
        $this->expectException(PriceException::class);
        $bookingManager->calculatePriceWithoutOptions($booking);
    }

    public function testCreateBookingOptions(): void
    {
        $roomType = new RoomType();
        $room = new Room();
        for ($i = 0; $i < 3; ++$i) {
            $roomType->addRoomOption(new RoomOption());
        }
        $room->setRoomType($roomType);

        /** @var Booking&PHPUnit_Framework_MockObject_MockObject $booking */
        $booking = $this->getMockBuilder(Booking::class)->setMethods(['getRoom'])->getMock();
        $booking->method('getRoom')->willReturn($room);
        $booking->expects($this->exactly(2))->method('getRoom');

        $this->bookingManager->createBookingOptions($booking);

        $this->assertCount(3, $booking->getBookingOptions());

        $room->setRoomType(null);
        $booking->method('getRoom')->willReturn($room);

        $this->expectException(\LogicException::class);

        $this->bookingManager->createBookingOptions($booking);
    }

    public function testCalculateHTPrice(): void
    {
        $this->calculatorMock->method('calculateTotalPrice')->willReturn(1000);
        $this->calculatorMock->expects($this->once())
            ->method('calculateTotalPrice')
            ->with($this->isInstanceOf(Booking::class))
        ;

        $booking = new Booking();
        $this->bookingManager->calculateHTPrice($booking);

        $this->assertEquals(1000, $booking->getTotalHT());
    }

    public function testCreateBookingAndCalculatePriceWithoutOptions(): void
    {
        $roomType = new RoomType();
        $room = new Room();
        for ($i = 0; $i < 3; ++$i) {
            $roomType->addRoomOption(new RoomOption());
        }
        $room->setRoomType($roomType);

        $roomRepoMock = $this->getMockBuilder(RoomRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])->getMock();
        $roomRepoMock->method('find')->willReturn($room);

        $this->emMock->method('getRepository')->willReturn($roomRepoMock);

        $this->calculatorMock->method('calculateTotalPriceWithoutOptions')->willReturn(1000);
        $this->calculatorMock->expects($this->once())
            ->method('calculateTotalPriceWithoutOptions')
            ->with($this->isInstanceOf(Booking::class))
        ;

        $booking = $this->bookingManager->createBookingAndCalculatePriceWithoutOptions(1, $this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d'));

        $this->assertEquals(1000, $booking->getTotalHTWithoutOptions());
    }

    public function testHandleBookingOptionsRequest(): void
    {
        $this->calculatorMock->method('calculateTotalPrice')->willReturn(1000);
        $this->calculatorMock->expects($this->once())
            ->method('calculateTotalPrice')
            ->with($this->isInstanceOf(Booking::class))
        ;

        $booking = new Booking();
        $this->bookingManager->handleBookingOptionsRequest($booking);

        $this->assertEquals(1000, $booking->getTotalHT());
    }

    public function testHandleCreateRequest(): void
    {
        $this->emMock->expects($this->once())->method('persist')->with($this->isInstanceOf(Booking::class));
        $this->emMock->expects($this->once())->method('flush');
        $booking = new Booking();
        $this->bookingManager->handleCreateRequest($booking);
    }
}
