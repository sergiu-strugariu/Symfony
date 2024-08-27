<?php

namespace App\Repository;

use App\Entity\CompanyReview;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyReview>
 *
 * @method CompanyReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyReview[]    findAll()
 * @method CompanyReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyReview::class);
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countReviews(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.deletedAt IS NULL')
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
    public function findReviewsByFilters(string $column, string $dir, $keyword, DateTimeInterface $startDate = null, DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->leftJoin('r.company', 'c')
            ->select("
                r.id,
                r.uuid,
                r.name,
                r.surname,
                r.email,
                r.status,
                r.totalValuesStar,
                c.name as companyName,
                DATE_FORMAT(r.startDate, '%d-%m-%Y') as startDate,
                DATE_FORMAT(r.endDate, '%d-%m-%Y') as endDate,
                DATE_FORMAT(r.createdAt, '%d-%m-%Y') as createdAt"
            )
            ->where('r.deletedAt IS NULL');

        // Field search
        $fields = [
            'r.id',
            'r.name',
            'r.surname',
            'r.email',
            'c.name'
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
                ->andWhere('r.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy($column === 'companyName' ? 'c.name' : "r.$column", $dir)
            ->getQuery()
            ->getResult();
    }
}
