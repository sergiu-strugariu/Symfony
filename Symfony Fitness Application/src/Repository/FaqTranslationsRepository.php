<?php

namespace App\Repository;

use App\Entity\FaqTranslations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqTranslations>
 *
 * @method FaqTranslations|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqTranslations|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqTranslations[]    findAll()
 * @method FaqTranslations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqTranslationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqTranslations::class);
    }

    //    /**
    //     * @return FAQTranslations[] Returns an array of FAQTranslations objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FAQTranslations
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
