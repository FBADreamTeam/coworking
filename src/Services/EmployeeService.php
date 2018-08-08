<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 26/07/2018
 * Time: 10:36.
 */

namespace App\Services;

use App\Entity\Employee;
use App\Entity\Role;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmployeeService
{
    /**
     * @var ObjectRepository
     */
    private $repo;

    /**
     * EmployeeService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Employee::class);
    }

    /**
     * @return array
     */
    public function getAllEmployees(): array
    {
        return $this->repo->findAll();
    }

    /**
     * @param Role $role
     *
     * @return array
     */
    public function getAllEmployeesWithRole(Role $role): array
    {
        return $this->repo->findBy(['role' => $role]);
    }
}
