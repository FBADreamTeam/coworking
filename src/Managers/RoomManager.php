<?php

namespace App\Managers;

use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;

class RoomManager
{
    private $em;

    /**
     * RoomManager constructor.
     *
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createRoom()
    {
    }

    public function editRoom()
    {
    }

    /**
     * Delete room.
     *
     * @param Room $room
     */
    public function deleteRoom(Room $room): void
    {
        $this->em->remove($room);
        $this->em->flush();
    }
}
