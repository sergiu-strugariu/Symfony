<?php

namespace App\Repository;

use App\Entity\EducationCategory;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationCategory>
 *
 * @method EducationCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationCategory[]    findAll()
 * @method EducationCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationCategory::class);
    }

    public function getCategories()
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getResult();
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
            ->join('c.educationCategoryTranslations', 'ct')
            ->select("
                c.id,
                c.uuid,
                c.fileName,
                ct.title,
                ct.description"
            )
            ->where('c.deletedAt IS NULL')
            ->andWhere('ct.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'c.id',
            'ct.title',
            'ct.description',
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

        $translationFields = match ($column) {
            'title' => 'ct.' . $column,
            default => 'c.' . $column,
        };

        return $queryBuilder
            ->orderBy($translationFields, $dir)
            ->getQuery()
            ->getResult();

    }
}
