<?php

namespace App\Repository;

use App\Entity\EventWinner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventWinner>
 *
 * @method EventWinner|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventWinner|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventWinner[]    findAll()
 * @method EventWinner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventWinnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventWinner::class);
    }

    //    /**
    //     * @return EventWinner[] Returns an array of EventWinner objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EventWinner
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
