<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 14/08/2018
 * Time: 16:29
 */

namespace App\Tests\entity;


use App\Entity\Employee;
use App\Entity\Role;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    public function testCreateEmployee()
    {
        $employee = new Employee();

        $this->assertInstanceOf(Employee::class, $employee);

        return $employee;
    }

    /**
     * @depends testCreateEmployee
     */
    public function testGetRole(Employee $employee)
    {
        $role = (new Role())->setLabel('ROLE_EMPLOYEE');

        $employee->setRole($role);

        $this->assertInstanceOf(Role::class, $employee->getRole());

        return $employee;
    }

    /**
     * @depends testGetRole
     */
    public function testGetLabelRoles(Employee $employee)
    {
        $role = (new Role())->setLabel('ROLE_ADMIN');

        $employee->setRole($role);

        $this->assertArraySubset(['ROLE_ADMIN'], $employee->getRoles());
    }
}