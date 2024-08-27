<?php

namespace App\Repository;

use App\Entity\CertificationCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CertificationCategory>
 *
 * @method CertificationCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CertificationCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CertificationCategory[]    findAll()
 * @method CertificationCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificationCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CertificationCategory::class);
    }

}
