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

    public function findAllWithRoomTypes()
    {
        return $this->createQueryBuilder('ro')
            ->select('ro.id, ro.label, ro.description, ro.price, rt.label AS rtlabel')
            ->join('ro.roomTypes', 'rt')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneRoomOptionWithRoomTypes($id_room_option)
    {
        return $this->createQueryBuilder('ro')
            ->select('ro.id, ro.label, ro.description, ro.price, rt.label AS rtlabel')
            ->join('ro.roomTypes', 'rt')
            ->where('ro.id = :optionRoom_id')
            ->setParameter('optionRoom_id', $id_room_option)
            ->getQuery()
            ->getResult()
            ;
    }
}
