<?php

namespace App\Repository;

use App\Entity\EducationSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationSchedule>
 *
 * @method EducationSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationSchedule[]    findAll()
 * @method EducationSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationSchedule::class);
    }

}
