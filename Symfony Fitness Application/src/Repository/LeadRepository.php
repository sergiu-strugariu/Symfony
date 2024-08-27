<?php

namespace App\Repository;

use App\Entity\Language;
use App\Entity\Lead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lead>
 *
 * @method Lead|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lead|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lead[]    findAll()
 * @method Lead[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }

    public function findTotalCount(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select("
                l.id,
                l.uuid,
                l.firstName,
                l.lastName,
                l.email,
                l.phone,
                l.companyDetails,
                l.interests"
            )
            ->where('l.deletedAt IS NULL');

        // Field search
        $fields = [
            'l.id',
            'l.firstName',
            'l.lastName',
            'l.email',
            'l.phone',
            'l.companyDetails',
            'l.interests',
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
                ->andWhere('l.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('l.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }
}
