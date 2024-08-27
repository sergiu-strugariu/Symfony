<?php

namespace App\Repository;

use App\Entity\PageWidgetTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageWidgetTranslation>
 *
 * @method PageWidgetTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageWidgetTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageWidgetTranslation[]    findAll()
 * @method PageWidgetTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageWidgetTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageWidgetTranslation::class);
    }

}
