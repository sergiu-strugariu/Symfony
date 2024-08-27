<?php

namespace App\Repository;

use App\Entity\PageWidget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageWidget>
 *
 * @method PageWidget|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageWidget|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageWidget[]    findAll()
 * @method PageWidget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageWidgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageWidget::class);
    }

}
