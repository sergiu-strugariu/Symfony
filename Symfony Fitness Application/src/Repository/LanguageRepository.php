<?php

namespace App\Repository;

use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Language>
 *
 * @method Language|null find($id, $lockMode = null, $lockVersion = null)
 * @method Language|null findOneBy(array $criteria, array $orderBy = null)
 * @method Language[]    findAll()
 * @method Language[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Language::class);
    }

    public function findTotalCount(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findActiveLanguages()
    {
        return $this->createQueryBuilder('l')
            ->where('l.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function findActiveLanguage($locale)
    {
        return $this->createQueryBuilder('l')
            ->where('l.deletedAt IS NULL')
            ->andWhere('l.locale = :locale') // Add your condition here
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();;
    }

    public function findByFilters(string $column, string $dir, $keyword, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select("
                l.id,
                l.name,
                l.locale"
            )
            ->where('l.deletedAt IS NULL');

        // Field search
        $fields = [
            'l.id',
            'l.name',
            'l.locale',
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
