<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 31/07/2018
 * Time: 17:29
 */

namespace App\Managers;

use App\Entity\Address;
use App\Entity\Customer;
use App\Events\UserCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CustomerManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return Customer[]|\App\Entity\Employee[]|object[]
     */
    public function listCustomer(): array
    {
        return $this->em->getRepository(Customer::class)->findAll();
    }

    /**
     * @param Customer $customer
     * @return void
     */
    public function createCustomer(Customer $customer): void
    {
        $this->em->persist($customer);
        $this->em->flush();
        $event = new UserCreatedEvent($customer);
        $this->dispatcher->dispatch(UserCreatedEvent::NAME, $event);
    }

    /**
     * @param Address $address
     * @return void
     */
    public function addAddressCustomer(Address $address): void
    {
        $this->em->persist($address);
        $this->em->flush();
    }

    /**
     * @return void
     */
    public function updateCustomer(): void
    {
        $this->em->flush();
    }

    /**
     * @param Address $address
     * @return void
     */
    public function deleteAddressCustomer(Address $address): void
    {
        $this->em->remove($address);
        $this->em->flush();
    }

    /**
     * Gestion des doublons par email
     * @param string $email
     * @return bool
     */
    public function checkDuplicateEmail(string $email): bool
    {
        return $this->em->getRepository(Customer::class)->findBy(['email' => $email]) ? true : false;
    }
}
