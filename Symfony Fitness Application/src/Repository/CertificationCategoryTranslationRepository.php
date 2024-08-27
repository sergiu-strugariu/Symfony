<?php

namespace App\Repository;

use App\Entity\CertificationCategoryTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CertificationCategoryTranslation>
 *
 * @method CertificationCategoryTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CertificationCategoryTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CertificationCategoryTranslation[]    findAll()
 * @method CertificationCategoryTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificationCategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CertificationCategoryTranslation::class);
    }

}
