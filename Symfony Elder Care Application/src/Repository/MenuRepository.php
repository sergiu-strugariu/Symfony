<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function getMenus()
    {
        return $this->createQueryBuilder('m')
            ->select("
            m.id,
            m.uuid,
            m.title,
            m.machineName
        ")
            ->where("m.deletedAt IS NULL")->getQuery()->getArrayResult();
    }

    public function getMenuItemsByMenu(Menu $menu)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('
                mi.id as id,
                m.id AS menuId,
                mi.cssClass,
                mi.weight,
                mi.icon,
                mi.link,
                mi.linkText,
                mi.description,
                parent.id AS parentId'
            )
            ->leftJoin('m.menuItems', 'mi')
            ->leftJoin('mi.menuItem', 'parent')
            ->where('m.deletedAt IS NULL')
            ->andWhere('m.id = :menu')
            ->setParameter('menu', $menu)
            ->orderBy('mi.weight', 'ASC');

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
