<?php

namespace App\Repository;

use App\Entity\EducationCategoryTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationCategoryTranslation>
 *
 * @method EducationCategoryTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationCategoryTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationCategoryTranslation[]    findAll()
 * @method EducationCategoryTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationCategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationCategoryTranslation::class);
    }

    //    /**
    //     * @return EducationCategoryTranslation[] Returns an array of EducationCategoryTranslation objects
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

    //    public function findOneBySomeField($value): ?EducationCategoryTranslation
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
