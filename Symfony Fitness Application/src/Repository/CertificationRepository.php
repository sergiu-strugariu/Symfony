<?php

namespace App\Repository;

use App\Entity\Certification;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certification>
 *
 * @method Certification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Certification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Certification[]    findAll()
 * @method Certification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certification::class);
    }

    public function findTotalCount(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, Language $language, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->join('c.certificationTranslations', 'ct')
            ->select("
                c.id,
                c.uuid,
                ct.title,
                ct.description
                "
            )
            ->where('c.deletedAt IS NULL')
            ->andWhere('ct.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'c.id',
            'ct.title',
            'ct.description'
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

        $translationFields = match ($column) {
            'title', 'description' => 'ct.' . $column,
            default => 'c.' . $column,
        };

        return $queryBuilder
            ->orderBy($translationFields, $dir)
            ->getQuery()
            ->getResult();

    }
}
