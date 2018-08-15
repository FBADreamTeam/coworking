<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingOptionsRepository")
 */
class BookingOptions implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"filter", "options"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(value="1", message="admin.booking_options.quantity.greater_than")
     * @Groups({"filter", "options"})
     * @var int
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RoomOption")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"filter", "options"})
     * @var RoomOption
     */
    private $roomOption;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Booking", inversedBy="bookingOptions")
     * @ORM\JoinColumn(nullable=false)
     * @var Booking
     */
    private $booking;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"filter", "options"})
     */
    private $total;

    public function getId()
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getRoomOption(): RoomOption
    {
        return $this->roomOption;
    }

    public function setRoomOption(RoomOption $roomOption): self
    {
        $this->roomOption = $roomOption;

        return $this;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function setBooking(Booking $booking): self
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Shortcut to get the RoomOption label directly
     * @return string
     */
    public function getLabel(): string
    {
        return $this->roomOption->getLabel();
    }

    /**
     * Shortcut to get the RoomOption description directly
     * @return string
     */
    public function getDescription(): string
    {
        return $this->roomOption->getDescription();
    }

    /**
     * Shortcut to get the RoomOption price directly
     * @return int
     */
    public function getPrice(): int
    {
        return $this->roomOption->getPrice();
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'roomOption' => $this->roomOption,
        ];
    }
}
