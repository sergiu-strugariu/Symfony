<?php

namespace App\Repository;

use App\Entity\CategoryCareTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryCareTranslation>
 *
 * @method CategoryCareTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryCareTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryCareTranslation[]    findAll()
 * @method CategoryCareTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryCareTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryCareTranslation::class);
    }

    //    /**
    //     * @return CategoryCareTranslation[] Returns an array of CategoryCareTranslation objects
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

    //    public function findOneBySomeField($value): ?CategoryCareTranslation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
