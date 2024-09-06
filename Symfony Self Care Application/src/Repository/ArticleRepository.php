<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\CategoryArticle;
use App\Entity\Language;
use App\Entity\User;
use App\Helper\DefaultHelper;
use DateTimeInterface;
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
    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    public function __construct(ManagerRegistry $registry, DefaultHelper $helper)
    {
        parent::__construct($registry, Article::class);
        $this->helper = $helper;
    }

    /**
     * @param User|null $user
     * @return float|bool|int|string|null
     */
    public function countArticles(User $user = null): float|bool|int|string|null
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.deletedAt IS NULL');

        // Check user exist
        if (isset($user)) {
            $queryBuilder
                ->andWhere('a.user = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param Language $language
     * @param User|null $user
     * @return array
     */
    public function findArticlesByFilters(string $column, string $dir, $keyword, Language $language, User $user = null): array
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('a')
            ->join('a.articleTranslations', 'at')
            ->select("
                a.id,
                a.uuid,
                a.slug,
                at.title,
                COALESCE(a.fileName, '$defaultImage') as fileName,
                a.status,
                DATE_FORMAT(a.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('a.deletedAt IS NULL')
            ->andWhere('at.language = :language')
            ->setParameter('language', $language);

        // Check user exist
        if (isset($user)) {
            $queryBuilder
                ->andWhere('a.user = :user')
                ->setParameter('user', $user);
        }

        // Field search
        $fields = [
            'a.id',
            'at.title',
            'a.status',
            'a.slug'
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
            ->orderBy($column === 'title' ? 'at.' . $column : 'a.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param object|null $article
     * @param int $limit
     * @return array
     */
    public function getArticles(object $article = null, int $limit = 3): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->join('a.categoryArticles', 'cat')
            ->where('a.deletedAt IS NULL')
            ->andWhere('a.status = :status');

        // Check exist article and set filter by categories
        if (isset($article)) {
            $queryBuilder
                ->andWhere('cat IN (:categories)')
                ->andWhere('a.id != :id')
                ->setParameter('categories', $article->getCategoryArticles())
                ->setParameter('id', $article->getId());
        }

        // Dynamic order by
        return $queryBuilder
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Language $language
     * @param CategoryArticle|null $category
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return bool|float|int|mixed|string|null
     */
    public function getArticlesByFilters(Language $language, CategoryArticle $category = null, int $limit = 9, int $offset = 0, bool $isCount = false): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->join('a.articleTranslations', 'at')
            ->where('a.deletedAt IS NULL')
            ->andWhere('at.language = :language')
            ->andWhere('a.status = :status')
            ->setParameter('language', $language)
            ->setParameter('status', Article::STATUS_PUBLISHED);

        // Filter by @categorySlug
        if (!empty($category)) {
            $queryBuilder
                ->andWhere(':category MEMBER OF a.categoryArticles')
                ->setParameter('category', $category);
        }


        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT a.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $queryBuilder
            ->select("at.title, at.shortDescription, a.slug, a.fileName")
            ->groupBy('a.id')
            ->orderBy('a.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function getSingleArticleByParams(string $slug = ''): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.slug = :slug')
            ->andWhere('a.deletedAt IS NULL')
            ->andWhere('a.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Article::STATUS_PUBLISHED);

        // Dynamic order by
        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
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
            ->join('art.categoryArticles', 'cat')
            ->where("art.id $dir :id")
            ->andWhere('cat IN (:categories)')
            ->andWhere('art.deletedAt IS NULL')
            ->andWhere('art.status = :status')
            ->setParameter('id', $article->getId())
            ->setParameter('categories', $article->getCategoryArticles())
            ->setParameter('status', $article::STATUS_PUBLISHED)
            ->orderBy('art.id', $order)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $year
     * @param User|null $user
     * @return mixed
     */
    public function getDataByMonthAndYear(int $year, User $user = null): mixed
    {
        $queryBuilder = $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count')
            ->where('YEAR(entity.createdAt) = :year')
            ->andWhere('entity.status = :status')
            ->andWhere('entity.deletedAt IS NULL');

        // Check exist filter user
        if (isset($user)) {
            $queryBuilder
                ->andWhere('entity.user = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->setParameter('year', $year)
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getDataYears(): array
    {
        return $this->createQueryBuilder('entity')
            ->select('DISTINCT YEAR(entity.createdAt) as year')
            ->andWhere('entity.deletedAt IS NULL')
            ->andWhere('entity.status = :status')
            ->setParameter('status', Article::STATUS_PUBLISHED)
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }
}