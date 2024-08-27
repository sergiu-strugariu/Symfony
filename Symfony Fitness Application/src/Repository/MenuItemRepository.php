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

        public function findByWeightDesc($language, $menuUuid = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select(
                'm.id',
                'IDENTITY(m.menu) AS menu_id',
                'IDENTITY(m.menuItem) AS menu_item_id',
                'IDENTITY(t.language) AS language_id',
                'm.cssClass',
                'm.image',
                'm.weight',
                't.id AS translation_id',
                't.linkText',
                't.link',
                't.description',
            )
            ->leftJoin('m.menuItemTranslations', 't')
            ->leftJoin('m.menu', 'menu')
            ->where('t.language = :language')
            ->setParameter('language', $language)
            ->orderBy('m.weight', 'ASC');

        if ($menuUuid !== null) {
            $qb->andWhere('menu.uuid = :menuUuid')
                ->setParameter('menuUuid', $menuUuid);
        }

        return $qb->getQuery()->getResult();
    }

}
