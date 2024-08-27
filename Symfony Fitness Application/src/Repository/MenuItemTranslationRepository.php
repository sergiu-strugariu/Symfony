<?php

namespace App\Repository;

use App\Entity\MenuItemTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuItemTranslation>
 *
 * @method MenuItemTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuItemTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuItemTranslation[]    findAll()
 * @method MenuItemTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuItemTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItemTranslation::class);
    }

}
