<?php

namespace App\Repository;

use App\Entity\Page;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countPages(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     * @return array
     */
    public function findPagesByFilters(string $column, string $dir, $keyword, DateTimeInterface $startDate = null, DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select("
                p.id,
                p.name,
                p.machineName,
                p.url,
                DATE_FORMAT(p.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('p.deletedAt IS NULL');

        // Field search
        $fields = [
            'p.id',
            'p.name',
            'p.machineName',
            'p.url'
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
                ->andWhere('p.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('p.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }
}
