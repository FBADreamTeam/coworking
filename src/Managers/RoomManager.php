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

    public function createRoom(Room $room)
    {
        //insertion en BDD
        $this->em->persist($room);
        $this->em->flush();
    }

    public function editRoom()
    {
        $this->em->flush();
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
