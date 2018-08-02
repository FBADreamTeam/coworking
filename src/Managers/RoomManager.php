<?php

namespace App\Managers;

use App\Entity\Room;
use App\Services\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * RoomManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param ImageUploader $uploader
     */
    public function __construct(EntityManagerInterface $em, ImageUploader $uploader)
    {
        $this->em = $em;
        $this->uploader = $uploader;
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
}
