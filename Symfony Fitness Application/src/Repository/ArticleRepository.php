<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function getArticlesChartStats()
    {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a.id) as count, DATE_FORMAT(a.createdAt, \'%d\') as day')
            ->where('a.createdAt >= :startOfMonth')
            ->setParameter('startOfMonth', (new \DateTime())->modify('first day of this month'))
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery();

        $result = $query->getResult();
        $daysInMonth = (int) (new \DateTime())->format('t');
        $counts['counts'] = array_fill(1, $daysInMonth, 0);

        foreach ($result as $row) {
            $counts['counts'][(int) $row['day']] = (int) $row['count'];
        }

        $counts['counts'] = array_values($counts['counts']);
        return $counts;
    }


    public function findTotalCount($status = null): float|bool|int|string|null
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.deletedAt IS NULL');

        if (null !== $status) {
            $queryBuilder->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, Language $language, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->join('a.articleTranslations', 'at')
            ->select("
                a.id,
                a.uuid,
                a.imageName,
                at.title,
                a.status"
            )
            ->where('a.deletedAt IS NULL')
            ->andWhere('at.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'a.id',
            'at.title',
            'a.status',
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
                ->andWhere('a.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $translationFields = match ($column) {
            'title' => 'at.' . $column,
            default => 'a.' . $column,
        };

        return $queryBuilder
            ->orderBy($translationFields, $dir)
            ->getQuery()
            ->getResult();

    }

    /**
     * @param Article $article
     * @param string $order
     * @return mixed
     */
    public function findNextPrevArticle(Article $article, string $order = 'ASC'): mixed
    {
        $dir = $order === 'ASC' ? '>' : '<';
        return $this->createQueryBuilder('art')
            ->where("art.id $dir :id")
            ->andWhere('art.deletedAt IS NULL')
            ->andWhere('art.status = :status')
            ->setParameter('id', $article->getId())
            ->setParameter('status', $article::STATUS_PUBLISHED)
            ->orderBy('art.id', $order)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
