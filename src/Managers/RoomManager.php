<?php

namespace App\Managers;

use App\Entity\Room;
use App\Entity\RoomType;
use App\Repository\RoomRepository;
use App\Services\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class RoomManager extends AbstractManager
{
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
     * @param ImageUploader          $uploader
     * @param SerializerInterface    $serializer
     */
    public function __construct(EntityManagerInterface $em, ImageUploader $uploader, SerializerInterface $serializer)
    {
        parent::__construct($em);
        $this->uploader = $uploader;
        $this->serializer = $serializer;
    }

    /**
     * @param Room $room
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
     */
    public function uploadImage(Room $room): void
    {
        // if we have an UploadedFile, user wants to update the featured image
        if ($room->getFeaturedImage() instanceof UploadedFile) {
            // we check if the article already has a featured image
            if (file_exists($this->uploader->getDirectory().$room->getFeaturedImage()) && is_file($this->uploader->getDirectory().$room->getFeaturedImage())) {
                // if it exists, we delete it
                unlink($this->uploader->getDirectory().$room->getFeaturedImage());
            }
            // Uploads the file
            $this->uploader->upload($room, 'featuredImage', 'name');
        }
    }

    /**
     * @param RoomType $type
     * @param string   $startDate
     * @param string   $endDate
     *
     * @return mixed
     */
    public function filterByType(RoomType $type, string $startDate, string $endDate)
    {
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        /** @var RoomRepository $repo */
        $repo = $this->em->getRepository(Room::class);

        return $repo->getAvailableBookingsByType($type, $startDateTime, $endDateTime);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->serializer->serialize($data, 'json', ['groups' => ['filter']]);
    }
}
