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
        $this->emMock->expects($this->exactly(2))->method('getRepository')->with($this->equalTo(Room::class));

        $booking = $this->bookingManager->createBookingFromRoomAndDates(1, '2018-08-18', '2018-08-19');

        $this->assertEquals(1, $booking->getRoom()->getId());
        $this->assertInstanceOf(\DateTimeInterface::class, $booking->getStartDate());
        $this->assertEquals('2018-08-18', $booking->getStartDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTimeInterface::class, $booking->getEndDate());
        $this->assertEquals('2018-08-19', $booking->getEndDate()->format('Y-m-d'));

        $this->expectException(BookingInvalidDatesException::class);
        $this->bookingManager->createBookingFromRoomAndDates(1, '2018-08-20', '2018-08-19');
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
        $this->calculatorMock->method('calculateTotalPriceWithoutOptions')->willReturn(1000);
        $this->calculatorMock->expects($this->once())
            ->method('calculateTotalPriceWithoutOptions')
            ->with($this->isInstanceOf(Booking::class))
        ;

        $booking = new Booking();
        $this->bookingManager->calculatePriceWithoutOptions($booking);

        $this->assertEquals(1000, $booking->getTotalHTWithoutOptions());
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

        $booking = $this->bookingManager->createBookingAndCalculatePriceWithoutOptions(1, '2018-08-19', '2018-08-20');

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
