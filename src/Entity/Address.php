<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 */
class Address
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.address.street.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.address.street.max_length")
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message="admin.address.zip.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.address.zip.max_length")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.address.city.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.address.city.max_length")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.address.country.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.address.country.max_length")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255", maxMessage="admin.address.cpl.max_length")
     */
    private $addressCpl;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="addresses")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="admin.address.customer.not_null")
     */
    private $customer;

    public function getId()
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getAddressCpl(): ?string
    {
        return $this->addressCpl;
    }

    public function setAddressCpl(?string $addressCpl): self
    {
        $this->addressCpl = $addressCpl;

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

    public function __toString(): string
    {
        return sprintf(
            '%s %s %s %s %s',
            $this->street,
            $this->addressCpl,
            $this->city,
            $this->postalCode,
            $this->country
        );
    }
}
