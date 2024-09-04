<?php

namespace App\Repository;

use App\Entity\Faq;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faq>
 *
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    public function findTotalCount($status = null): float|bool|int|string|null
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, Language $language, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->join('f.faqTranslations', 'qt')
            ->select("
                f.id,
                f.uuid,
                qt.question,
                DATE_FORMAT(f.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->andWhere('qt.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'f.id',
            'qt.question',
            'f.createdAt',
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
                ->andWhere('f.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $translationFields = match ($column) {
            'question' => 'qt.' . $column,
            default => 'f.' . $column,
        };

        return $queryBuilder
            ->orderBy($translationFields, $dir)
            ->getQuery()
            ->getResult();

    }

}
