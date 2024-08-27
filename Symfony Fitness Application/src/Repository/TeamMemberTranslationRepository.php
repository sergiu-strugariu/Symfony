<?php

namespace App\Repository;

use App\Entity\TeamMemberTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMemberTranslation>
 *
 * @method TeamMemberTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamMemberTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamMemberTranslation[]    findAll()
 * @method TeamMemberTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamMemberTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMemberTranslation::class);
    }

}
