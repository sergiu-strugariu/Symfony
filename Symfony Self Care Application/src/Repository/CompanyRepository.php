<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CompanyReview;
use App\Entity\County;
use App\Entity\User;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    public function __construct(ManagerRegistry $registry, DefaultHelper $helper)
    {
        parent::__construct($registry, Company::class);
        $this->helper = $helper;
    }

    /**
     * @param User|null $user
     * @param string $locationType
     * @return float|bool|int|string|null
     */
    public function countCompanies(User $user = null, string $locationType = Company::LOCATION_TYPE_CARE): float|bool|int|string|null
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.locationType = :type')
            ->setParameter('type', $locationType);

        if (isset($user)) {
            $queryBuilder
                ->andWhere('c.user = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param User|null $user
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param string $locationType
     * @return array
     */
    public function findCompaniesByFilters(string $column, string $dir, $keyword, string $locationType = Company::LOCATION_TYPE_CARE, User $user = null): array
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('c')
            ->select("
                c.id,
                c.uuid,
                c.name,
                c.slug,
                c.locationType,
                c.email,
                COALESCE(c.fileName, '$defaultImage') as fileName,
                c.status,
                DATE_FORMAT(c.createdAt, '%d-%m-%Y') as createdAt,
                DATE_FORMAT(c.updatedAt, '%d-%m-%Y') as updatedAt"
            )
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.locationType = :type')
            ->setParameter('type', $locationType);

        // Check user exist
        if (isset($user)) {
            $queryBuilder
                ->andWhere('c.user = :user')
                ->setParameter('user', $user);
        }

        // Field search
        $fields = [
            'c.id',
            'c.name',
            'c.email'
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
            ->orderBy('c.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $locationType
     * @param object|null $category
     * @param bool $count
     * @param int $limit
     * @return bool|float|int|mixed|string|null
     */
    public function getCompaniesByType(string $locationType, object $category = null, int $limit = 7, bool $count = false): mixed
    {
        // Query for filter results
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.county', 'cty')
            ->leftJoin('c.city', 'ciy')
            ->leftJoin('c.categoryCares', 'cc')
            ->leftJoin('c.categoryServices', 'cs')
            ->leftJoin('c.companyGalleries', 'gal')
            ->leftJoin('c.companyReviews', 'r', 'WITH', 'r.status = :approved')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.status = :status')
            ->andWhere('c.locationType = :type')
            ->setParameter('type', $locationType)
            ->setParameter('approved', CompanyReview::STATUS_APPROVED)
            ->setParameter('status', Company::STATUS_PUBLISHED);

        // Filter by @categorySlug
        if (!empty($category)) {
            switch ($locationType) {
                case Company::LOCATION_TYPE_CARE:
                    $queryBuilder
                        ->andWhere(':categoryCare MEMBER OF c.categoryCares')
                        ->setParameter('categoryCare', $category);
                    break;
                case Company::LOCATION_TYPE_PROVIDER:
                    $queryBuilder
                        ->andWhere(':categoryService MEMBER OF c.categoryServices')
                        ->setParameter('categoryService', $category);
                    break;
            }
        }

        if ($count) {
            return $queryBuilder
                ->select("COUNT(DISTINCT c.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $queryBuilder
            ->select("
                c.id,
                c.name,
                c.slug,
                COALESCE(c.fileName, :defaultImage) as fileName,
                c.averageRating,
                c.locationType,
                cty.name as county,
                ciy.name as city"
            )
            ->setParameter('defaultImage', $this->helper->getEnvValue('app_default_image'))
            ->setMaxResults($limit)
            ->orderBy('c.id', 'DESC')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $slug
     * @param string $type
     * @return mixed
     */
    public function getSingleCompanyBySlug(string $slug = '', string $type = Company::LOCATION_TYPE_CARE): mixed
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->andWhere('c.deletedAt IS NULL')
            ->andWhere('c.status = :status')
            ->andWhere('c.locationType = :type')
            ->setParameter('slug', $slug)
            ->setParameter('status', Company::STATUS_PUBLISHED)
            ->setParameter('type', $type);

        // Dynamic order by
        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Company $company
     * @param string $type
     * @param int $limit
     * @return mixed
     */
    public function getCompanyByCounty(Company $company, string $type = Company::LOCATION_TYPE_CARE, int $limit = 4): mixed
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.id != :id')
            ->andWhere('c.status = :status')
            ->andWhere('c.locationType = :type')
            ->andWhere('c.county = :county')
            ->setParameter('id', $company->getId())
            ->setParameter('county', $company->getCounty())
            ->setParameter('status', Company::STATUS_PUBLISHED)
            ->setParameter('type', $type)
            ->setMaxResults($limit);

        // Dynamic order by
        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Company $company
     * @param object|null $category
     * @param int $limit
     * @return bool|float|int|mixed|string|null
     */
    public function getCompaniesByCategory(Company $company, object $category = null, int $limit = 4): mixed
    {
        // Query for filter results
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.county', 'cty')
            ->leftJoin('c.city', 'ciy')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.id != :id')
            ->andWhere('c.status = :status')
            ->andWhere('c.locationType = :type')
            ->setParameter('id', $company->getId())
            ->setParameter('type', $company->getLocationType())
            ->setParameter('status', Company::STATUS_PUBLISHED);

        // Filter by @categorySlug
        if (!empty($category)) {
            switch ($company->getLocationType()) {
                case Company::LOCATION_TYPE_CARE:
                    $queryBuilder
                        ->andWhere(':categoryCare MEMBER OF c.categoryCares')
                        ->setParameter('categoryCare', $category);
                    break;
                case Company::LOCATION_TYPE_PROVIDER:
                    $queryBuilder
                        ->andWhere(':categoryService MEMBER OF c.categoryServices')
                        ->setParameter('categoryService', $category);
                    break;
            }
        }

        return $queryBuilder
            ->setMaxResults($limit)
            ->orderBy('c.id', 'DESC')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param object|null $category
     * @param County|null $county
     * @param string $sort
     * @param string $order
     * @param string $locationType
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return bool|float|int|mixed|string|null
     */
    public function getCompaniesByFilters(object $category = null, County $county = null, string $sort = 'id', string $order = 'DESC', string $locationType = Company::LOCATION_TYPE_CARE, int $limit = 10, int $offset = 0, bool $isCount = false): mixed
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.companyGalleries', 'gal')
            ->leftJoin('c.county', 'cty')
            ->leftJoin('c.city', 'ciy')
            ->leftJoin('c.companyReviews', 'r', 'WITH', 'r.status = :approved')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.status = :status')
            ->andWhere('c.locationType = :type')
            ->setParameter('status', Company::STATUS_PUBLISHED)
            ->setParameter('approved', CompanyReview::STATUS_APPROVED)
            ->setParameter('type', $locationType);

        // Filter by @category
        if (!empty($category)) {
            switch ($locationType) {
                case Company::LOCATION_TYPE_CARE:
                    $queryBuilder
                        ->andWhere(':categoryCare MEMBER OF c.categoryCares')
                        ->setParameter('categoryCare', $category);
                    break;
                case Company::LOCATION_TYPE_PROVIDER:
                    $queryBuilder
                        ->andWhere(':categoryService MEMBER OF c.categoryServices')
                        ->setParameter('categoryService', $category);
                    break;
            }
        }

        // Filter by @county
        if (!empty($county)) {
            $queryBuilder
                ->andWhere('c.county = :county')
                ->setParameter('county', $county);
        }

        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT c.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $queryBuilder
            ->select("
                c.id,
                c.name,
                c.price,
                c.shortDescription,
                c.slug,
                COALESCE(c.fileName, :defaultImage) as fileName,
                COUNT(DISTINCT r.review) AS averageCount,
                c.averageRating,
                r.review,
                c.locationType,
                cty.name as county,
                ciy.name as city,
                GROUP_CONCAT(DISTINCT gal.fileName SEPARATOR ',') AS images"
            )
            ->setParameter('defaultImage', $this->helper->getEnvValue('app_default_image'))
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->groupBy('c.id')
            ->orderBy("c.$sort", $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $year
     * @param User|null $user
     * @param string $locationType
     * @return mixed
     */
    public function getDataByMonthAndYear(int $year, User $user = null, string $locationType = Company::LOCATION_TYPE_CARE): mixed
    {
        $queryBuilder = $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count')
            ->where('YEAR(entity.createdAt) = :year')
            ->andWhere('entity.status = :status')
            ->andWhere('entity.deletedAt IS NULL')
            ->andWhere('entity.locationType = :locationType');

        // Check exist filter user
        if (isset($user)) {
            $queryBuilder
                ->andWhere('entity.user = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder
            ->setParameter('status', Company::STATUS_PUBLISHED)
            ->setParameter('locationType', $locationType)
            ->setParameter('year', $year)
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $locationType
     * @return array
     */
    public function getDataYears(string $locationType = Company::LOCATION_TYPE_CARE): array
    {
        return $this->createQueryBuilder('entity')
            ->select('DISTINCT YEAR(entity.createdAt) as year')
            ->andWhere('entity.status = :status')
            ->andWhere('entity.deletedAt IS NULL')
            ->andWhere('entity.locationType = :locationType')
            ->setParameter('locationType', $locationType)
            ->setParameter('status', Company::STATUS_PUBLISHED)
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @param string $locationType
     * @return array
     */
    public function getAllCompanyByType(string $locationType = Company::LOCATION_TYPE_CARE): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.status = :status')
            ->andWhere('c.locationType = :type')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->setParameter('type', $locationType)
            ->getQuery()
            ->getResult();
    }
}