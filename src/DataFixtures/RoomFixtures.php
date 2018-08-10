<?php

namespace App\DataFixtures;

use App\Entity\Room;
use App\Entity\RoomOption;
use App\Entity\RoomType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RoomFixtures extends Fixture
{
    private $roomOptions;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager  $manager)
    {
        $this->addRoomDesk($manager);
        $this->addRoomConference($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $om
     */
    private function addRoomDesk(ObjectManager $om)
    {
        $roomType = new RoomType();
        $roomType->setLabel('Bureau');
        $roomType->setRoomOptions($this->addRoomOptions($om, $roomType));
        $om->persist($roomType);

        for ($i = 0; $i < 5; ++$i) {
            $room = new Room();
            $room->setName('bureau n°'.($i+1));
            $room->setCapacity(10);
            $room->setDailyPrice(1000);
            $room->setHourlyPrice(200);
            $room->setWeeklyPrice(4000);
            $room->setMonthlyPrice(14000);
            $room->setDescription('Super joli bureau');
            $room->setStatus('dispo');
            $room->setRoomType($roomType);
            $om->persist($room);
        }
    }

    /**
     * @param ObjectManager $om
     */
    private function addRoomConference(ObjectManager $om): void
    {
        $roomType = new RoomType();
        $roomType->setLabel('Salle de réunion');
        $roomType->setRoomOptions($this->addRoomOptions($om, $roomType));
        $om->persist($roomType);

        for ($i = 0; $i < 5; ++$i) {
            $room = new Room();
            $room->setName('Salle n°'.($i+1));
            $room->setCapacity(1000);
            $room->setDailyPrice(1000);
            $room->setHourlyPrice(200);
            $room->setWeeklyPrice(4000);
            $room->setMonthlyPrice(14000);
            $room->setDescription('Super jolie salle');
            $room->setStatus('dispo');
            $room->setRoomType($roomType);
            $om->persist($room);
        }
    }

    /**
     * @param ObjectManager $om
     * @param RoomType      $roomType
     */
    private function addRoomOptions(ObjectManager $om, RoomType $roomType)
    {
        for ($i = 0; $i < 5; ++$i) {
            $roomOption = new RoomOption();
            $roomOption->setLabel('Option n°'.$i);
            $roomOption->setDescription('Description de l\'option');
            $roomOption->setPrice(1000);
            $roomOption->addRoomType($roomType);
            $om->persist($roomOption);

            $this->roomOptions[] = $roomOption;
        }

        return $this->roomOptions;
    }
}
