<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmployeeRepository")
 */
class Employee extends AbstractUser
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Role")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    /**
     * Employee constructor.
     * @param null|string $firstName
     * @param null|string $lastName
     * @param null|string $email
     * @param null|string $password
     * @param Role|null $role
     */
    public function __construct(?string $firstName = null, ?string $lastName = null, ?string $email = null, ?string $password = null, ?Role $role = null)
    {
        parent::__construct($firstName, $lastName, $email, $password);
        $this->setRole($role);
    }


    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @return null
     */
    public function getRoles()
    {
        return [$this->role->getLabel()];
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }
}
