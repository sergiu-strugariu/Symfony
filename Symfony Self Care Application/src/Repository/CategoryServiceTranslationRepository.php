<?php

namespace App\Repository;

use App\Entity\CategoryServiceTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryServiceTranslation>
 *
 * @method CategoryServiceTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryServiceTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryServiceTranslation[]    findAll()
 * @method CategoryServiceTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryServiceTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryServiceTranslation::class);
    }

    //    /**
    //     * @return CategoryServiceTranslation[] Returns an array of CategoryServiceTranslation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CategoryServiceTranslation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
