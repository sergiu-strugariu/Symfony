<?php

namespace App\Repository;

use App\Entity\TeamMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMember>
 *
 * @method TeamMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamMember[]    findAll()
 * @method TeamMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMember::class);
    }

    public function findAllMembers()
    {
        return $this->createQueryBuilder('tm')
            ->where('tm.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }
    
    public function findTotalCount(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('tm')
            ->select('COUNT(tm.id)')
            ->where('tm.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('tm')
            ->select("
                tm.id,
                tm.uuid,
                tm.name"
            )
            ->where('tm.deletedAt IS NULL');

        // Field search
        $fields = [
            'tm.id',
            'tm.name'
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
                ->andWhere('tm.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('tm.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

}
