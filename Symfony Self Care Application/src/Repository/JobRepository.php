<?php

namespace App\Repository;

use App\Entity\CategoryJob;
use App\Entity\County;
use App\Entity\Job;
use App\Entity\Language;
use App\Entity\User;
use App\Helper\DefaultHelper;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Job>
 *
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository
{
    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    /**
     * @param ManagerRegistry $registry
     * @param DefaultHelper $helper
     */
    public function __construct(ManagerRegistry $registry, DefaultHelper $helper)
    {
        parent::__construct($registry, Job::class);
        $this->helper = $helper;
    }

    /**
     * @param User|null $user
     * @return float|bool|int|string|null
     */
    public function countJobs(User $user = null): float|bool|int|string|null
    {
        $queryBuilder = $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->where('j.deletedAt IS NULL');

        // Check exist user
        if (isset($user)) {
            $queryBuilder
                ->andWhere('j.user = :user')
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
    public function findJobsByFilters(string $column, string $dir, $keyword, Language $language, User $user = null): array
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('j')
            ->join('j.county', 'c')
            ->join('j.city', 's')
            ->join('j.jobTranslations', 'jt')
            ->select("
                j.id,
                j.uuid,
                jt.title,
                COALESCE(j.fileName, '$defaultImage') as fileName,
                j.slug,
                j.jobType,
                c.name as county,
                s.name as city,
                j.status,
                DATE_FORMAT(j.createdAt, '%d-%m-%Y') as createdAt"
            )
            ->where('j.deletedAt IS NULL')
            ->andWhere('jt.language = :language')
            ->setParameter('language', $language);

        // Check user exist
        if (isset($user)) {
            $queryBuilder
                ->andWhere('j.user = :user')
                ->setParameter('user', $user);
        }

        // Field search
        $fields = [
            'j.id',
            'jt.title',
            'c.name',
            's.name',
            'j.status',
            'j.slug',
            'j.jobType'
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

        // Check type and set entity
        $relationField = match ($column) {
            'title' => 'jt.' . $column,
            'county' => 'c.name',
            'city' => 's.name',
            default => 'j.' . $column,
        };

        // Dynamic order by
        return $queryBuilder
            ->orderBy($relationField, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Language $language
     * @param CategoryJob|null $category
     * @param County|null $county
     * @param string $sort
     * @param string $order
     * @param string $contractType
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return bool|float|int|mixed|string|null
     * @throws Exception
     */
    public function getJobsByFilters(
        Language    $language,
        CategoryJob $category = null,
        County      $county = null,
        string      $sort = 'id',
        string      $order = 'DESC',
        string      $contractType = '',
        int         $limit = 6,
        int         $offset = 0,
        bool        $isCount = false): mixed
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('j')
            ->leftJoin('j.jobTranslations', 'jt')
            ->leftJoin('j.categoryJobs', 'cat')
            ->leftJoin('cat.categoryJobTranslations', 'ctr')
            ->leftJoin('j.county', 'cty')
            ->leftJoin('j.city', 'ciy')
            ->leftJoin('j.company', 'c')
            ->where('j.deletedAt IS NULL')
            ->andWhere('jt.language = :language')
            ->andWhere('j.status = :status')
            ->setParameter('status', Job::STATUS_PUBLISHED)
            ->setParameter('language', $language);

        // Filter by @category
        if (!empty($category)) {
            $queryBuilder
                ->andWhere(':categoryJob MEMBER OF j.categoryJobs')
                ->setParameter('categoryJob', $category);
        }

        // Filter by @county
        if (!empty($county)) {
            $queryBuilder
                ->andWhere('j.county = :county')
                ->setParameter('county', $county);
        }

        // Filter by @contractType
        if (!empty($contractType)) {
            $queryBuilder
                ->andWhere('j.jobType = :contractType')
                ->setParameter('contractType', $contractType);
        }

        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT j.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        $results = $queryBuilder
            ->select("
                j.id,
                jt.title,
                j.slug,
                j.jobType,
                jt.shortDescription,
                j.address,
                c.name as companyName,
                COALESCE(c.logo, '$defaultImage') as fileName,
                cty.name as county,
                ciy.name as city,
                j.createdAt,
                ctr.title as category"
            )
            ->groupBy('j.id')
            ->orderBy("j.$sort", $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        foreach ($results as &$result) {
            if (isset($result['createdAt'])) {
                $result['createdAt'] = $this->helper->getTimeAgo($result['createdAt']);
            }
        }

        return $results;
    }

    /**
     * @param Language $language
     * @param Job|null $job
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return bool|float|int|mixed|string|null
     */
    public function getRecommendedJobs(Language $language, Job $job = null, int $limit = 6, int $offset = 0, bool $isCount = false): mixed
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('j')
            ->leftJoin('j.jobTranslations', 'jt')
            ->leftJoin('j.categoryJobs', 'cat')
            ->leftJoin('cat.categoryJobTranslations', 'ctr')
            ->leftJoin('j.county', 'cty')
            ->leftJoin('j.city', 'ciy')
            ->leftJoin('j.company', 'c')
            ->where('j.deletedAt IS NULL')
            ->andWhere('jt.language = :language')
            ->andWhere('j.status = :status')
            ->setParameter('status', Job::STATUS_PUBLISHED)
            ->setParameter('language', $language);

        // Filter by @category
        if (!empty($job)) {
            $queryBuilder
                ->andWhere(':categoryJob MEMBER OF j.categoryJobs')
                ->andWhere('j.id != :id')
                ->setParameter('categoryJob', $job->getCategoryJobs()->first())
                ->setParameter('id', $job->getId());
        }

        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT j.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $queryBuilder
            ->select("
                j.id,
                jt.title,
                j.slug,
                j.jobType,
                jt.shortDescription,
                j.address,
                c.name as companyName,
                COALESCE(c.logo, '$defaultImage') as fileName,
                cty.name as county,
                ciy.name as city,
                ctr.title as category"
            )
            ->groupBy('j.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function getSingleJobByParams(string $slug = ''): mixed
    {
        $queryBuilder = $this->createQueryBuilder('j')
            ->where('j.slug = :slug')
            ->andWhere('j.deletedAt IS NULL')
            ->andWhere('j.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Job::STATUS_PUBLISHED);

        // Dynamic order by
        return $queryBuilder
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
            ->setParameter('status', Job::STATUS_PUBLISHED)
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
            ->setParameter('status', Job::STATUS_PUBLISHED)
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return array
     */
    public function getAllJobs(): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.deletedAt IS NULL')
            ->andWhere('j.status = :status')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->getQuery()
            ->getResult();
    }
}
