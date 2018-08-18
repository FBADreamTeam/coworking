<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="admin.booking.start_date.not_blank")
     * @Assert\DateTime(message="admin.booking.start_date.valid")
     *
     * @var \DateTimeInterface
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="admin.booking.end_date.not_blank")
     * @Assert\DateTime(message="admin.booking.end_date.valid")
     *
     * @var \DateTimeInterface
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Room
     */
    private $room;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="bookings")
     *
     * @var Customer
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookingOptions", mappedBy="booking", orphanRemoval=true, cascade={"persist", "remove"})
     *
     * @var BookingOptions[]|ArrayCollection
     */
    private $bookingOptions;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", mappedBy="booking", cascade={"persist", "remove"})
     *
     * @var Order
     */
    private $order;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $totalHTWithoutOptions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $totalHT;

    /**
     * Booking constructor.
     */
    public function __construct()
    {
        $this->bookingOptions = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param \DateTimeInterface $startDate
     *
     * @return Booking
     */
    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param \DateTimeInterface $endDate
     *
     * @return Booking
     */
    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Room
     */
    public function getRoom(): Room
    {
        return $this->room;
    }

    /**
     * @param Room|null $room
     *
     * @return Booking
     */
    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return Customer|null
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     *
     * @return Booking
     */
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection|BookingOptions[]
     */
    public function getBookingOptions(): Collection
    {
        return $this->bookingOptions;
    }

    /**
     * Return an associative array of RoomOption, where the key is the roomOption id
     * and the value the roomOption object itself ([roomOption->id] = roomOption).
     *
     * @return array
     */
    public function getRoomOptionsAsHashedArray(): array
    {
        // initialize options array
        $options = [];
        /** @var BookingOptions $bookingOption */
        foreach ($this->bookingOptions as $bookingOption) {
            // get the roomOption associated with the bookingOption
            $roomOption = $bookingOption->getRoomOption();
            // add the roomOption at its id key in the options array
            $options[$roomOption->getId()] = $roomOption;
        }

        return $options;
    }

    /**
     * @param BookingOptions $bookingOption
     *
     * @return Booking
     */
    public function addBookingOption(BookingOptions $bookingOption): self
    {
        if (!$this->bookingOptions->contains($bookingOption)) {
            $this->bookingOptions[] = $bookingOption;
            $bookingOption->setBooking($this);
        }

        return $this;
    }

    /**
     * @param BookingOptions $bookingOption
     *
     * @return Booking
     */
    public function removeBookingOption(BookingOptions $bookingOption): self
    {
        if ($this->bookingOptions->contains($bookingOption)) {
            $this->bookingOptions->removeElement($bookingOption);
            // set the owning side to null (unless already changed)
            // Booking can't be null...
//            if ($bookingOption->getBooking() === $this) {
//                $bookingOption->setBooking(null);
//            }
        }

        return $this;
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return Booking
     */
    public function setOrder(Order $order): self
    {
        $this->order = $order;

        // set the owning side of the relation if necessary
        if ($this !== $order->getBooking()) {
            $order->setBooking($this);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotalHTWithoutOptions(): ?int
    {
        return $this->totalHTWithoutOptions;
    }

    /**
     * @param int|null $totalHTWithoutOptions
     *
     * @return Booking
     */
    public function setTotalHTWithoutOptions(?int $totalHTWithoutOptions): self
    {
        $this->totalHTWithoutOptions = $totalHTWithoutOptions;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTotalHT(): ?int
    {
        return $this->totalHT;
    }

    /**
     * @param int|null $totalHT
     *
     * @return Booking
     */
    public function setTotalHT(?int $totalHT): self
    {
        $this->totalHT = $totalHT;

        return $this;
    }
}
