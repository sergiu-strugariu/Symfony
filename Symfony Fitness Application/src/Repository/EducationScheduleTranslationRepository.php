<?php

namespace App\Repository;

use App\Entity\EducationScheduleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationScheduleTranslation>
 *
 * @method EducationScheduleTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationScheduleTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationScheduleTranslation[]    findAll()
 * @method EducationScheduleTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationScheduleTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationScheduleTranslation::class);
    }

}
