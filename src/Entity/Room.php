<?php

namespace App\Entity;

use App\Exceptions\PriceException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
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
     * @Assert\NotBlank(message="admin.room.name.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.room.name.max_length")
     * @Groups({"filter"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value="0", message="admin.room.capacity.greater_than")
     * @Groups({"filter"})
     */
    private $capacity;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="admin.room.desc.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.room.desc.max_length")
     * @Groups({"filter"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.room.status.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.room.status.max_length")
     * @Groups({"filter"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value="0", message="admin.room.hourly_price.greater_than")
     * @Groups({"filter", "options"})
     */
    private $hourlyPrice;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value="0", message="admin.room.daily_price.greater_than")
     * @Groups({"filter", "options"})
     */
    private $dailyPrice;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value="0", message="admin.room.weekly_price.greater_than")
     * @Groups({"filter"})
     */
    private $weeklyPrice;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(value="0", message="admin.room.monthly_price.greater_than")
     * @Groups({"filter"})
     */
    private $monthlyPrice;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="room")
     */
    private $bookings;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RoomType", inversedBy="rooms")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"filter"})
     */
    private $roomType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"filter"})
     */
    private $featuredImage;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHourlyPrice(): ?int
    {
        return $this->hourlyPrice;
    }

    /**
     * @param int $hourlyPrice
     *
     * @return Room
     *
     * @throws PriceException
     */
    public function setHourlyPrice(int $hourlyPrice): self
    {
        $this->checkPrice($hourlyPrice);
        $this->hourlyPrice = $hourlyPrice;

        return $this;
    }

    public function getDailyPrice(): ?int
    {
        return $this->dailyPrice;
    }

    /**
     * @param int $dailyPrice
     *
     * @return Room
     *
     * @throws PriceException
     */
    public function setDailyPrice(int $dailyPrice): self
    {
        $this->checkPrice($dailyPrice);
        $this->dailyPrice = $dailyPrice;

        return $this;
    }

    public function getWeeklyPrice(): ?int
    {
        return $this->weeklyPrice;
    }

    /**
     * @param int $weeklyPrice
     *
     * @return Room
     *
     * @throws PriceException
     */
    public function setWeeklyPrice(int $weeklyPrice): self
    {
        $this->checkPrice($weeklyPrice);
        $this->weeklyPrice = $weeklyPrice;

        return $this;
    }

    public function getMonthlyPrice(): ?int
    {
        return $this->monthlyPrice;
    }

    /**
     * @param int $monthlyPrice
     *
     * @return Room
     *
     * @throws PriceException
     */
    public function setMonthlyPrice(int $monthlyPrice): self
    {
        $this->checkPrice($monthlyPrice);
        $this->monthlyPrice = $monthlyPrice;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setRoom($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getRoom() === $this) {
                $booking->setRoom(null);
            }
        }

        return $this;
    }

    public function getRoomType(): ?RoomType
    {
        return $this->roomType;
    }

    public function setRoomType(?RoomType $roomType): self
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * @return string|UploadedFile
     */
    public function getFeaturedImage()
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage($featuredImage): self
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }

    /**
     * @param int $price
     *
     * @throws PriceException
     */
    private function checkPrice(int $price): void
    {
        if ($price <= 0) {
            throw new PriceException();
        }
    }
}
