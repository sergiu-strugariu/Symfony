<?php

namespace App\Repository;

use App\Entity\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuItem>
 *
 * @method MenuItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuItem[]    findAll()
 * @method MenuItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    public function add(MenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findByWeightDesc($menu = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select(
                'm.id',
                'IDENTITY(m.menu) AS menu_id',
                'IDENTITY(m.menuItem) AS menu_item_id',
                'm.cssClass',
                'm.icon',
                'm.weight',
                'm.id AS translation_id',
                'm.linkText',
                'm.link',
                'm.description',
            )
            ->leftJoin('m.menu', 'menu')
            ->orderBy('m.weight', 'ASC');

        if ($menu !== null) {
            $qb->andWhere('menu.machineName = :menu')
                ->setParameter('menu', $menu);
        }

        return $qb->getQuery()->getResult();
    }

}
