<?php

namespace App\Repository;

use App\Entity\BookingOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BookingOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingOptions[]    findAll()
 * @method BookingOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingOptionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BookingOptions::class);
    }

//    /**
//     * @return BookingOptions[] Returns an array of BookingOptions objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BookingOptions
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
