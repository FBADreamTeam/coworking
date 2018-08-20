<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @ApiResource(
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={
 *     "get",
 *     "user_info"={
 *       "user_info"="user_info"
 *     },
 * })
 */
class Customer extends AbstractUser
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Address", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="customer")
     */
    private $bookings;

    /**
     * Customer constructor.
     *
     * @param null|string $firstName
     * @param null|string $lastName
     * @param null|string $email
     * @param null|string $password
     */
    public function __construct(?string $firstName = null, ?string $lastName = null, ?string $email = null, ?string $password = null)
    {
        parent::__construct($firstName, $lastName, $email, $password);
        $this->addresses = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    /**
     * @return Collection|Address[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setCustomer($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getCustomer() === $this) {
                $address->setCustomer(null);
            }
        }

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
            $booking->setCustomer($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getCustomer() === $this) {
                $booking->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }
}
