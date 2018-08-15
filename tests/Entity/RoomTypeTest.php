<?php

namespace App\Tests\Entity;

use App\Entity\Room;
use App\Entity\RoomOption;
use App\Entity\RoomType;
use PHPUnit\Framework\TestCase;

class RoomTypeTest extends TestCase
{
    /**
     * Test to Instanciate Entity.
     */
    public function testRoomCanBeCreated(): void
    {
        $roomOption = new RoomType();
        $this->assertInstanceOf(RoomType::class, $roomOption);
    }

    /**
     * Try to add a booking to a room.
     *
     * @return RoomType
     */
    public function testInsertRoom(): RoomType
    {
        $roomType = new RoomType();
        $room = new Room();
        $room2 = new Room();

        $roomType->addRoom($room);

        $this->assertCount(1, $roomType->getRooms());
        $roomType->addRoom($room2);

        $this->assertCount(2, $roomType->getRooms());

        return $roomType;
    }

    /**
     * @depends testInsertRoom
     *
     * @param RoomType $roomType
     */
    public function testRemoveRoom(RoomType $roomType): void
    {
        $roomType->removeRoom($roomType->getRooms()[0]);
        $this->assertCount(1, $roomType->getRooms());
        $roomType->removeRoom($roomType->getRooms()[1]);
        $this->assertCount(0, $roomType->getRooms());
    }

    /**
     * Try to add a booking to a room.
     *
     * @return RoomType
     */
    public function testInsertOption(): RoomType
    {
        $roomType = new RoomType();
        $roomOption = new RoomOption();
        $roomOption2 = new RoomOption();

        $roomType->addRoomOption($roomOption);

        $this->assertCount(1, $roomType->getRoomOptions());
        $roomType->addRoomOption($roomOption2);

        $this->assertCount(2, $roomType->getRoomOptions());

        return $roomType;
    }

    /**
     * @depends testInsertOption
     *
     * @param RoomType $roomType
     */
    public function testRemoveOption(RoomType $roomType): void
    {
        $roomType->removeRoomOption($roomType->getRoomOptions()[0]);
        $this->assertCount(1, $roomType->getRoomOptions());
        $roomType->removeRoomOption($roomType->getRoomOptions()[1]);
        $this->assertCount(0, $roomType->getRoomOptions());
    }
}
