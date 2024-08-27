<?php

namespace App\Repository;

use App\Entity\CertificationTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CertificationTranslation>
 *
 * @method CertificationTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CertificationTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CertificationTranslation[]    findAll()
 * @method CertificationTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificationTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CertificationTranslation::class);
    }

}
