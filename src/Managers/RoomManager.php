<?php

namespace App\Managers;

use App\Entity\Room;
use App\Entity\RoomType;
use App\Services\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class RoomManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ImageUploader
     */
    private $uploader;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * RoomManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param ImageUploader $uploader
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $em, ImageUploader $uploader, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->uploader = $uploader;
        $this->serializer = $serializer;
    }

    /**
     * @param Room $room
     * @return void
     */
    public function createRoom(Room $room): void
    {
        // Image management
        $this->uploadImage($room);
        //insertion en BDD
        $this->em->persist($room);
        $this->em->flush();
    }

    /**
     * @param Room $room
     * @return void
     */
    public function editRoom(Room $room): void
    {
        $this->uploadImage($room);
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

    /**
     * @param Room $room
     * @return void
     */
    public function uploadImage(Room $room): void
    {
        // if we have an UploadedFile, user wants to update the featured image
        if ($room->getFeaturedImage() instanceof UploadedFile) {
            // we check if the article already has a featured image
            if (file_exists($this->uploader->getDirectory() . $room->getFeaturedImage()) && is_file($this->uploader->getDirectory() . $room->getFeaturedImage())) {
                // if it exists, we delete it
                unlink($this->uploader->getDirectory() . $room->getFeaturedImage());
            }
            // Uploads the file
            $this->uploader->upload($room, 'featuredImage', 'name');
        }
    }

    /**
     * @param RoomType $type
     * @param string $startDate
     * @param string $endDate
     * @return mixed
     */
    public function filterByType(RoomType $type, string $startDate, string $endDate)
    {
        // SELECT * FROM room r0_
        // LEFT JOIN booking b1_ ON r0_.id = b1_.room_id
        // WHERE r0_.room_type_id = 3
        // AND
        // (
        //  (b1_.start_date >= "2018-08-01 12:00:00" AND b1_.end_date <= "2018-08-01 11:00:00")
        //      OR
        //  (b1_.start_date IS NULL AND b1_.end_date IS NULL)
        // )

//        $repo = $this->em->getRepository(Room::class);
//        $query = $repo->createQueryBuilder('r')
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select('r')
            ->from(Room::class, 'r')
            ->leftJoin('r.bookings', 'b')
            ->where('r.roomType = :type')
            ->andWhere('(b.startDate >= :endDate AND b.endDate <= :startDate) OR (b.startDate IS NULL AND b.endDate IS NULL)')
            ->setParameters([
                'type' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate
            ])
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @return mixed
     */
    public function filter(string $startDate, string $endDate)
    {
        // SELECT * FROM room r0_
        // LEFT JOIN booking b1_ ON r0_.id = b1_.room_id
        // WHERE
        //  (b1_.start_date >= "2018-08-01 12:00:00" AND b1_.end_date <= "2018-08-01 11:00:00")
        //      OR
        //  (b1_.start_date IS NULL AND b1_.end_date IS NULL)
        // ORDER BY r0_.roomType

//        $repo = $this->em->getRepository(Room::class);
//        $query = $repo->createQueryBuilder('r')
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select('r')
            ->from(Room::class, 'r')
            ->leftJoin('r.bookings', 'b')
            ->where('(b.startDate >= :endDate AND b.endDate <= :startDate) OR (b.startDate IS NULL AND b.endDate IS NULL)')
            ->setParameters([
                'startDate' => $startDate,
                'endDate' => $endDate
            ])
            ->orderBy('r.roomType')
            ->getQuery()
        ;

//        dd($query->getSQL());

        return $query->getResult();
    }

    /**
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->serializer->serialize($data, 'json', ['groups' => ['filter']]);
    }
}
