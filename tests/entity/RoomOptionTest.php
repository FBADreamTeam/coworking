<?php

namespace App\Tests\entity;

use App\Entity\RoomOption;
use App\Entity\RoomType;
use PHPUnit\Framework\TestCase;

class RoomOptionTest extends TestCase
{
    /**
     * Test to Instanciate Entity.
     */
    public function testRoomCanBeCreated(): void
    {
        $roomOption = new RoomOption();
        $this->assertInstanceOf(RoomOption::class, $roomOption);
    }

    /**
     * Test option price can't be 0 or negative.
     */
    public function testOptionPriceIsBiggerThanZero(): void
    {
        $room = new RoomOption();
        $this->expectException('LogicException');

        $room->setPrice(0);
    }

    /**
     * Try to add a booking to a room.
     *
     * @return RoomOption
     */
    public function testInsertOption(): RoomOption
    {
        $roomOption = new RoomOption();
        $roomType = new RoomType();
        $roomType2 = new RoomType();

        $roomOption->addRoomType($roomType);

        $this->assertCount(1, $roomOption->getRoomTypes());
        $roomOption->addRoomType($roomType2);

        $this->assertCount(2, $roomOption->getRoomTypes());

        return $roomOption;
    }

    /**
     * @depends testInsertOption
     *
     * @param RoomOption $roomOption
     */
    public function testRemoveOption(RoomOption $roomOption): void
    {
        $roomOption->removeRoomType($roomOption->getRoomTypes()[0]);
        $this->assertCount(1, $roomOption->getRoomTypes());
        $roomOption->removeRoomType($roomOption->getRoomTypes()[1]);
        $this->assertCount(0, $roomOption->getRoomTypes());
    }
}
