<?php

namespace App\Repository;

use App\Entity\Language;
use DateTimeInterface;
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

    /**
     * @return float|bool|int|string|null
     */
    public function countLanguages(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @return array
     */
    public function findLanguagesByFilters(string $column, string $dir, $keyword): array
    {
        $queryBuilder = $this
            ->createQueryBuilder('l')
            ->select("l.id, l.name, l.locale")
            ->where('l.deletedAt IS NULL');

        // Field search
        $fields = [
            'l.id',
            'l.name',
            'l.locale'
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

        // Dynamic order by
        return $queryBuilder
            ->orderBy('l.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }
}
