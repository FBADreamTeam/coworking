<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 31/07/2018
 * Time: 17:29
 */

namespace App\Managers;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;

class CustomerManager
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function listCustomer()
    {
        return $this->em->getRepository(Customer::class)->findAll();
    }

    public function createCustomer($employee)
    {
        $this->em->persist($employee);
        $this->em->flush();
    }

    public function addAddressCustomer($address)
    {
        $this->em->persist($address);
        $this->em->flush();
    }

    public function updateCustomer()
    {
        $this->em->flush();
    }

    public function deleteCustomer($id)
    {

        if ($this->em->find(Customer::class, $id)) {
            $this->em->remove($id);
            $this->em->flush();
            return true;
        } else {
            return false;
        }

    }

    public function deleteAddressCustomer($address)
    {
        $this->em->remove($address);
        $this->em->flush();
    }

    // Gestion des doublons par email
    public function checkDuplicateEmail($email)
    {
        return ($this->em->getRepository(Customer::class)->findByEmail($email)) ? true : false;
    }


}