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

        $deskName = [
            'Moineau',
            'Chouette',
            'Cygogne',
            'Corneille',
            'Goélan',
        ];

        for ($i = 0; $i < 5; ++$i) {
            $room = new Room();
            $room->setName('bureau '.$deskName[$i]);
            $room->setCapacity(10);
            $room->setDailyPrice(1000);
            $room->setHourlyPrice(200);
            $room->setWeeklyPrice(4000);
            $room->setMonthlyPrice(14000);
            $room->setDescription('Super joli bureau');
            $room->setStatus('dispo');
            $room->setFeaturedImage('bureau-amenage.jpeg');
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

        $roomName = [
            'Picasso',
            'Van Gogh',
            'Cézanne',
            'Monet',
            'Gauguin',
        ];

        for ($i = 0; $i < 5; ++$i) {
            $room = new Room();
            $room->setName('Salle '.$roomName[$i]);
            $room->setCapacity(1000);
            $room->setDailyPrice(1000);
            $room->setHourlyPrice(200);
            $room->setWeeklyPrice(4000);
            $room->setMonthlyPrice(14000);
            $room->setDescription('Super jolie salle');
            $room->setStatus('dispo');
            $room->setFeaturedImage('conference-room-beautiful-view.jpg');
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
        $optionName = [
            'Ordinateur',
            'Bureau(x) supplémentaire(s)',
            'Petit-déjeuner',
            'Forfait impression',
            'Téléphone',
        ];
        for ($i = 0; $i < 5; ++$i) {
            $roomOption = new RoomOption();
            $roomOption->setLabel($optionName[$i]);
            $roomOption->setDescription('Description de l\'option');
            $roomOption->setPrice(1000);
            $roomOption->addRoomType($roomType);
            $om->persist($roomOption);

            $this->roomOptions[] = $roomOption;
        }

        return $this->roomOptions;
    }
}
