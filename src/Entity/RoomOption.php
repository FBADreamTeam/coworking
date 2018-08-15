<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomOptionRepository")
 */
class RoomOption implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"filter", "options"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.room_option.label.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.room_option.label.max_length")
     * @Groups({"filter", "options"})
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="admin.room_option.desc.not_blank")
     * @Groups({"filter", "options"})
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value="0", message="admin.room_option.price.greater_than")
     * @Groups({"filter", "options"})
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\RoomType", inversedBy="roomOptions")
     */
    private $roomTypes;

    public function __construct()
    {
        $this->roomTypes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        if ($price <= 0) {
            throw new \LogicException('The price cannot be negative.');
        }
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|RoomType[]
     */
    public function getRoomTypes(): Collection
    {
        return $this->roomTypes;
    }

    public function addRoomType(RoomType $roomType): self
    {
        if (!$this->roomTypes->contains($roomType)) {
            $this->roomTypes[] = $roomType;
        }

        return $this;
    }

    public function removeRoomType(RoomType $roomType): self
    {
        if ($this->roomTypes->contains($roomType)) {
            $this->roomTypes->removeElement($roomType);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'description' => $this->description,
            'price' => $this->price,
            'roomTypes' => $this->roomTypes->map(function ($type) {
                return $type->getId();
            }),
        ];
    }
}
