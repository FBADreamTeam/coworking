<?php

namespace App\Managers;

use App\Entity\RoomOption;
use Doctrine\ORM\EntityManagerInterface;

class RoomOptionManager
{
    private $em;

    /**
     * RoomManager constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param RoomOption $roomOption
     */
    public function createRoomOption(RoomOption $roomOption): void
    {
        //insertion en BDD
        $this->em->persist($roomOption);
        $this->em->flush();
    }

    public function editRoomOption(): void
    {
        $this->em->flush();
    }

    /**
     * Delete room.
     *
     * @param RoomOption $roomOption
     */
    public function deleteRoomOption(RoomOption $roomOption): void
    {
        $this->em->remove($roomOption);
        $this->em->flush();
    }
}
