<?php

namespace App\Repository;

use App\Entity\Language;
use App\Entity\Menu;
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

    /**
     * @param $language
     * @param $machineName
     * @return array
     */
    public function findByWeightDesc($language, $machineName): array
    {
        return $this->createQueryBuilder('m')
            ->select(
                'm.id',
                'IDENTITY(m.menuItem) AS menu_item_id',
                'm.cssClass',
                'm.icon',
                'm.weight',
                't.linkText',
                't.link',
                't.description'
            )
            ->leftJoin('m.menuItemTranslations', 't')
            ->leftJoin('m.menu', 'menu')
            ->where('t.language = :language')
            ->andWhere('menu.machineName = :machineName')
            ->setParameter('language', $language)
            ->setParameter('machineName', $machineName)
            ->orderBy('m.weight', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
