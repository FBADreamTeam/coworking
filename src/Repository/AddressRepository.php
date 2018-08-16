<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Address|null find($id, $lockMode = null, $lockVersion = null)
 * @method Address|null findOneBy(array $criteria, array $orderBy = null)
 * @method Address[]    findAll()
 * @method Address[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /**
     * @param Customer $customer
     *
     * @return mixed
     */
    public function getAddressesFromCustomer(Customer $customer)
    {
        return $this->createQueryBuilder('a')
            ->where('a.customer = :customer')
            ->setParameter('customer', $customer)
        ;
    }
}
