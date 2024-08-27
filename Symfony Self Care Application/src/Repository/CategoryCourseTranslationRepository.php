<?php

namespace App\Repository;

use App\Entity\CategoryCourseTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryCourseTranslation>
 *
 * @method CategoryCourseTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryCourseTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryCourseTranslation[]    findAll()
 * @method CategoryCourseTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryCourseTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryCourseTranslation::class);
    }

    //    /**
    //     * @return CategoryCourseTranslation[] Returns an array of CategoryCourseTranslation objects
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

    //    public function findOneBySomeField($value): ?CategoryCourseTranslation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
