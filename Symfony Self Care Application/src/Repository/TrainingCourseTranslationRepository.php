<?php

namespace App\Repository;

use App\Entity\TrainingCourseTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainingCourseTranslation>
 *
 * @method TrainingCourseTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingCourseTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingCourseTranslation[]    findAll()
 * @method TrainingCourseTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingCourseTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingCourseTranslation::class);
    }
}
