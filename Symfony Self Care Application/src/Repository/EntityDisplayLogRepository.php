<?php

namespace App\Repository;

use App\Entity\EntityDisplayLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntityDisplayLog>
 *
 * @method EntityDisplayLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityDisplayLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityDisplayLog[]    findAll()
 * @method EntityDisplayLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityDisplayLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityDisplayLog::class);
    }

    //    /**
    //     * @return EntityDisplayLog[] Returns an array of EntityDisplayLog objects
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

    //    public function findOneBySomeField($value): ?EntityDisplayLog
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
