<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\RoomType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @param RoomType  $type
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return mixed
     */
    public function getBookingsIntersectingDatesByType(RoomType $type, \DateTime $startDate, \DateTime $endDate)
    {
//        SELECT r0_.id FROM room r0_ LEFT JOIN booking b1_ ON r0_.id = b1_.room_id WHERE r0_.room_type_id = 23 AND (
//            (b1_.start_date < ":startDate" AND b1_.end_date > ":startDate")
//            OR
//            (b1_.start_date >= ":startDate" AND b1_.end_date <= ":endDate")
//            OR
//            (b1_.start_date < ":endDate" AND b1_.end_date >= ":endDate")
//            OR
//            (b1_.start_date <= ":startDate" AND b1_.end_date >= ":endDate")
//        ) ORDER BY r0_.id ASC
        $query = $this->createQueryBuilder('r')
            ->select('r.id')
            ->leftJoin('r.bookings', 'b')
            ->where('r.roomType = :type')
            ->andWhere('(b.startDate < :startDate AND b.endDate > :startDate) OR (b.startDate >= :startDate AND b.endDate <= :endDate) OR (b.startDate < :endDate AND b.endDate >= :endDate) OR (b.startDate <= :startDate AND b.endDate >= :endDate)')
            ->setParameters([
                'type' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('r.id')
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * @param RoomType  $type
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return mixed
     */
    public function getAvailableBookingsByType(RoomType $type, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.roomType = :roomType')
            ->setParameter('roomType', $type)
        ;

        $intersects = \array_column($this->getBookingsIntersectingDatesByType($type, $startDate, $endDate), 'id');

        if (!empty($intersects)) {
            $qb->andWhere($qb->expr()->notIn('r.id', ':ids'))
                ->setParameter('ids', $intersects)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
