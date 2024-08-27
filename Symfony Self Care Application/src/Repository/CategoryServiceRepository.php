<?php

namespace App\Repository;

use App\Entity\CategoryService;
use App\Entity\Company;
use App\Entity\Language;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryService>
 *
 * @method CategoryService|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryService|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryService[]    findAll()
 * @method CategoryService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryService::class);
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countCategories(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param Language $language
     * @param string $type
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     * @return array
     */
    public function findCategoriesByFilters(string $column, string $dir, $keyword, Language $language, string $type, DateTimeInterface $startDate = null, DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->join('c.' . $type, 'ct')
            ->select("
                c.id,
                c.uuid,
                ct.title,
                c.slug,
                c.status,
                DATE_FORMAT(c.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('c.deletedAt IS NULL')
            ->andWhere('ct.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'c.id',
            'ct.title',
            'c.status',
            'c.slug'
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
                ->andWhere('c.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy($column === 'title' ? 'ct.' . $column : 'c.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->createQueryBuilder('cat')
            ->leftJoin('cat.companies', 'c')
            ->where('cat.deletedAt IS NULL')
            ->andWhere('cat.status = :status')
            ->andWhere('c.status = :status')
            ->andWhere('c.id IS NOT NULL')
            ->setParameter('status', Company::STATUS_PUBLISHED)
            ->orderBy('cat.id', 'DESC')
            ->groupBy('cat.id')
            ->getQuery()
            ->getResult();
    }
}
