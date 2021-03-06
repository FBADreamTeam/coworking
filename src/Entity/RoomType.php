<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomTypeRepository")
 */
class RoomType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"filter"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.room_type.label.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.room_type.label.max_length")
     * @Groups({"filter"})
     */
    private $label;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\RoomOption", mappedBy="roomTypes")
     */
    private $roomOptions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Room", mappedBy="roomType")
     */
    private $rooms;

    public function __construct()
    {
        $this->roomOptions = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|RoomOption[]
     */
    public function getRoomOptions(): Collection
    {
        return $this->roomOptions;
    }

    public function setRoomOptions($roomOptions)
    {
        $this->roomOptions = $roomOptions;

        return $this;
    }

    public function addRoomOption(RoomOption $roomOption): self
    {
        if (!$this->roomOptions->contains($roomOption)) {
            $this->roomOptions[] = $roomOption;
            $roomOption->addRoomType($this);
        }

        return $this;
    }

    public function removeRoomOption(RoomOption $roomOption): self
    {
        if ($this->roomOptions->contains($roomOption)) {
            $this->roomOptions->removeElement($roomOption);
            $roomOption->removeRoomType($this);
        }

        return $this;
    }

    /**
     * @return Collection|Room[]
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms[] = $room;
            $room->setRoomType($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->contains($room)) {
            $this->rooms->removeElement($room);
            // set the owning side to null (unless already changed)
            if ($room->getRoomType() === $this) {
                $room->setRoomType(null);
            }
        }

        return $this;
    }
}
