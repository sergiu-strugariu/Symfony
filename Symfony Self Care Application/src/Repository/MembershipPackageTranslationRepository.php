<?php

namespace App\Repository;

use App\Entity\MembershipPackageTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembershipPackageTranslation>
 *
 * @method MembershipPackageTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MembershipPackageTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MembershipPackageTranslation[]    findAll()
 * @method MembershipPackageTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembershipPackageTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipPackageTranslation::class);
    }

    //    /**
    //     * @return MembershipPackageTranslation[] Returns an array of MembershipPackageTranslation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MembershipPackageTranslation
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
