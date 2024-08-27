<?php

namespace App\Repository;

use App\Entity\PageSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageSection>
 *
 * @method PageSection|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageSection|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageSection[]    findAll()
 * @method PageSection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageSection::class);
    }

}
