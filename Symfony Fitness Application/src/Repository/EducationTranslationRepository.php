<?php

namespace App\Repository;

use App\Entity\EducationTranslation;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationTranslation>
 *
 * @method EducationTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationTranslation[]    findAll()
 * @method EducationTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationTranslation::class);
    }

}
