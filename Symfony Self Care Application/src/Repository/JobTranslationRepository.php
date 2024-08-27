<?php

namespace App\Repository;

use App\Entity\JobTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobTranslation>
 *
 * @method JobTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobTranslation[]    findAll()
 * @method JobTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobTranslation::class);
    }

    //    /**
    //     * @return JobTranslation[] Returns an array of JobTranslation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?JobTranslation
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
