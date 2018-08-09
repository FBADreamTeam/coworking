<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractUser implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.user.firstname.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.user.firstname.max_length")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.user.lastname.not_blank")
     * @Assert\Length(max="255", maxMessage="admin.user.lastname.max_length")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="admin.user.email.not_blank")
     * @Assert\Email(message="admin.user.email.valid")
     * @Assert\Length(max="255", maxMessage="admin.user.email.max_length")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="admin.user.password.not_blank")
     * @Assert\Length(min="8", minMessage="admin.user.password.min_length")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    private $expiredToken;

    /**
     * AbstractUser constructor.
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $email
     * @param string|null $password
     */
    public function __construct(?string $firstName = null, ?string $lastName = null, ?string $email = null, ?string $password = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
    }


    public function getId()
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getCreatedToken()
    {
        return $this->createdToken;
    }

    /**
     * @param mixed $createdToken
     */
    public function setCreatedToken($createdToken)
    {
        $this->createdToken = $createdToken;
    }

    /**
     * @return mixed
     */
    public function getExpiredToken()
    {
        return $this->expiredToken;
    }

    /**
     * @param mixed $expiredToken
     */
    public function setExpiredToken($expiredToken)
    {
        $this->expiredToken = $expiredToken;
    }
}
