<?php

namespace App\Repository;

use App\Entity\RoomOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RoomOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoomOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoomOption[]    findAll()
 * @method RoomOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomOptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RoomOption::class);
    }

//    /**
//     * @return RoomOption[] Returns an array of RoomOption objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RoomOption
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
