<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 31/07/2018
 * Time: 17:29.
 */

namespace App\Managers;

use App\Entity\Employee;

class EmployeeManager extends AbstractManager
{
    public function createEmployee($employee)
    {
        $this->em->persist($employee);
        $this->em->flush();
    }

    public function updateEmployee()
    {
        $this->em->flush();
    }

    public function deleteEmployee($id)
    {
        if (null !== $this->em->find(Employee::class, $id)) {
            $this->em->remove($id);
            $this->em->flush();

            return true;
        } else {
            return false;
        }
    }

    // Gestion des doublons de l'email
    public function checkDuplicateEmail($email)
    {
        return ($this->em->getRepository(Employee::class)->findBy(['email' => $email])) ? true : false;
    }
}
