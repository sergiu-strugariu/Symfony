<?php

namespace App\Repository;

use App\Entity\CategoryArticleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryArticleTranslation>
 *
 * @method CategoryArticleTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryArticleTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryArticleTranslation[]    findAll()
 * @method CategoryArticleTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryArticleTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryArticleTranslation::class);
    }

    //    /**
    //     * @return CategoryArticleTranslation[] Returns an array of CategoryArticleTranslation objects
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

    //    public function findOneBySomeField($value): ?CategoryArticleTranslation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
