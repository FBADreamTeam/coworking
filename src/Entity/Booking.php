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
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="admin.booking.start_date.not_blank")
     * @Assert\DateTime(message="admin.booking.start_date.valid")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="admin.booking.end_date.not_blank")
     * @Assert\DateTime(message="admin.booking.end_date.valid")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookingOptions", mappedBy="booking", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $bookingOptions;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Order", mappedBy="booking", cascade={"persist", "remove"})
     */
    private $order;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalHTWithoutOptions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalHT;

    public function __construct()
    {
        $this->bookingOptions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

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
     * @return array
     */
    public function getRoomOptionsAsHashedArray(): array
    {
        $options = [];
        /** @var BookingOptions $bookingOption */
        foreach ($this->bookingOptions as $bookingOption) {
            $roomOption = $bookingOption->getRoomOption();
            $options[$roomOption->getId()] = $roomOption;
        }

        return $options;
    }

    public function addBookingOption(BookingOptions $bookingOption): self
    {
        if (!$this->bookingOptions->contains($bookingOption)) {
            $this->bookingOptions[] = $bookingOption;
            $bookingOption->setBooking($this);
        }

        return $this;
    }

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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        // set the owning side of the relation if necessary
        if ($this !== $order->getBooking()) {
            $order->setBooking($this);
        }

        return $this;
    }

    public function getTotalHTWithoutOptions(): ?int
    {
        return $this->totalHTWithoutOptions;
    }

    public function setTotalHTWithoutOptions(?int $totalHTWithoutOptions): self
    {
        $this->totalHTWithoutOptions = $totalHTWithoutOptions;

        return $this;
    }

    public function getTotalHT(): ?int
    {
        return $this->totalHT;
    }

    public function setTotalHT(?int $totalHT): self
    {
        $this->totalHT = $totalHT;

        return $this;
    }
}
