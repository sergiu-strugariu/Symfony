<?php

namespace App\Repository;

use App\Entity\Language;
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

    public function findTotalCount(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMenuItemsByMenuOrderedByWeight(int $menuId): array
    {
        return $this->createQueryBuilder('mi')
            ->where('mi.menuItems = :menuId')
            ->setParameter('menuId', $menuId)
            ->orderBy('mi.weight', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select("
                m.id,
                m.uuid,
                m.title,
                m.machineName"
            )
            ->where('m.deletedAt IS NULL');

        // Field search
        $fields = [
            'm.id',
            'm.title',
            'm.machineName',
        ];

        // Check @keyword
        if (isset($keyword)) {
            $orExpr = $queryBuilder->expr()->orX();

            // Search in all fields
            foreach ($fields as $field) {
                $orExpr->add($queryBuilder->expr()->like($field, ':keyword'));
            }

            $queryBuilder
                ->andWhere($orExpr)
                ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($startDate && $endDate) {
            $queryBuilder
                ->andWhere('m.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('m.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Language $lang
     * @param Menu $menu
     * @return array
     */
    public function getMenuItemsByMenu(Language $lang, Menu $menu): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('
                mi.id as id,
                m.id AS menuId,
                mi.cssClass,
                mi.weight,
                mi.image,
                mt.link,
                mt.linkText,
                mt.description,
                parent.id AS parentId'
            )
            ->leftJoin('m.menuItems', 'mi')
            ->leftJoin('mi.menuItemTranslations', 'mt')
            ->leftJoin('mi.menuItem', 'parent')
            ->where('m.deletedAt IS NULL')
            ->andWhere('m.id = :menu')
            ->andWhere('mt.language = :lang')
            ->setParameter('menu', $menu)
            ->setParameter('lang', $lang)
            ->orderBy('mi.weight', 'ASC');

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
