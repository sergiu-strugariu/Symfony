<?php

namespace App\Repository;

use App\Entity\CategoryCourse;
use App\Entity\County;
use App\Entity\Language;
use App\Entity\TrainingCourse;
use App\Entity\User;
use App\Helper\DefaultHelper;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends ServiceEntityRepository<TrainingCourse>
 *
 * @method TrainingCourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingCourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingCourse[]    findAll()
 * @method TrainingCourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingCourseRepository extends ServiceEntityRepository
{
    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(ManagerRegistry $registry, DefaultHelper $helper, TranslatorInterface $translator)
    {
        parent::__construct($registry, TrainingCourse::class);
        $this->helper = $helper;
        $this->translator = $translator;
    }

    /**
     * @param User|null $user
     * @return float|bool|int|string|null
     */
    public function countTrainingCourse(User $user = null): float|bool|int|string|null
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.deletedAt IS NULL');

        if (isset($user)) {
            $queryBuilder
                ->andWhere('t.user = :user')
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
    public function findTrainingCourseByFilters(string $column, string $dir, $keyword, Language $language, User $user = null): array
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('t')
            ->join('t.county', 'c')
            ->join('t.city', 's')
            ->join('t.trainingCourseTranslations', 'tc')
            ->select("
                t.id,
                t.uuid,
                tc.title,
                t.price,
                COALESCE(t.fileName, '$defaultImage') as fileName,
                t.slug,
                c.name as county,
                s.name as city,
                t.status,
                DATE_FORMAT(t.createdAt, '%d-%m-%Y') as createdAt",
            )
            ->where('t.deletedAt IS NULL')
            ->andWhere('tc.language = :language')
            ->setParameter('language', $language);

        // Check user exist
        if (isset($user)) {
            $queryBuilder
                ->andWhere('t.user = :user')
                ->setParameter('user', $user);
        }

        // Field search
        $fields = [
            't.id',
            'tc.title',
            't.price',
            't.status',
            'c.name',
            's.name',
            't.slug'
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
            'title', 'price' => 'tc.' . $column,
            'county' => 'c.name',
            'city' => 's.name',
            default => 't.' . $column,
        };

        // Dynamic order by
        return $queryBuilder
            ->addGroupBy('t.id')
            ->orderBy($relationField, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Language $language
     * @param CategoryCourse|null $category
     * @param County|null $county
     * @param string $sort
     * @param string $order
     * @param string $format
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return bool|float|int|mixed|string|null
     * @throws Exception
     */
    public function getCourseByFilters(Language $language, CategoryCourse $category = null, County $county = null, string $sort = 'id', string $order = 'DESC', string $format = '', int $limit = 6, int $offset = 0, bool $isCount = false): mixed
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('course')
            ->leftJoin('course.trainingCourseTranslations', 'translation')
            ->leftJoin('course.categoryCourses', 'cat')
            ->leftJoin('cat.categoryCourseTranslations', 'categoryTrans')
            ->leftJoin('course.county', 'cty')
            ->leftJoin('course.company', 'company')
            ->where('course.deletedAt IS NULL')
            ->andWhere('translation.language = :language')
            ->andWhere('course.status = :status')
            ->setParameter('status', TrainingCourse::STATUS_PUBLISHED)
            ->setParameter('language', $language);

        // Filter by @category
        if (!empty($category)) {
            $queryBuilder
                ->andWhere(':categoryCourse MEMBER OF course.categoryCourses')
                ->setParameter('categoryCourse', $category);
        }

        // Filter by @county
        if (!empty($county)) {
            $queryBuilder
                ->andWhere('course.county = :county')
                ->setParameter('county', $county);
        }

        // Filter by @contractType
        if (!empty($format)) {
            $queryBuilder
                ->andWhere('course.format = :formatValue')
                ->setParameter('formatValue', $format);
        }

        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT course.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $queryBuilder
            ->select("
                course.id,
                course.slug,
                translation.title,
                CASE
                    WHEN course.format = 'physical' THEN :physical
                    WHEN course.format = 'online' THEN :online
                    ELSE course.format
                END as format,
                translation.level,
                translation.certificate,
                company.name as companyName,
                COALESCE(company.logo, :defaultImage) as fileName,
                cty.name as county,
                categoryTrans.title as category"
            )
            ->setParameter('physical', $this->translator->trans('physical', [], 'messages'))
            ->setParameter('online', $this->translator->trans('online', [], 'messages'))
            ->setParameter('defaultImage', $defaultImage)
            ->groupBy('course.id')
            ->orderBy("course.$sort", $order)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Language $language
     * @param TrainingCourse|null $course
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return bool|float|int|mixed|string|null
     */
    public function getRecommendedCourses(Language $language, TrainingCourse $course = null, int $limit = 6, int $offset = 0, bool $isCount = false): mixed
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.trainingCourseTranslations', 'tc')
            ->leftJoin('c.categoryCourses', 'cat')
            ->leftJoin('cat.categoryCourseTranslations', 'ctr')
            ->leftJoin('c.county', 'cty')
            ->leftJoin('c.city', 'ciy')
            ->leftJoin('c.company', 'company')
            ->where('c.deletedAt IS NULL')
            ->andWhere('tc.language = :language')
            ->andWhere('c.status = :status')
            ->setParameter('status', TrainingCourse::STATUS_PUBLISHED)
            ->setParameter('language', $language);

        // Filter by @category
        if (!empty($course)) {
            $queryBuilder
                ->andWhere(':categoryCourse MEMBER OF c.categoryCourses')
                ->andWhere('c.id != :id')
                ->setParameter('categoryCourse', $course->getCategoryCourses()->first())
                ->setParameter('id', $course->getId());
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
                tc.title,
                c.slug,
                CASE
                    WHEN c.format = 'physical' THEN :physical
                    WHEN c.format = 'online' THEN :online
                    ELSE c.format
                END as format,
                tc.level,
                tc.certificate,
                company.name as companyName,
                COALESCE(company.logo, :defaultImage) as fileName,
                cty.name as county,
                ctr.title as category"
            )
            ->setParameter('physical', $this->translator->trans('physical', [], 'messages'))
            ->setParameter('online', $this->translator->trans('online', [], 'messages'))
            ->setParameter('defaultImage', $defaultImage)
            ->groupBy('c.id')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function getSingleCourseByParams(string $slug = ''): mixed
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->andWhere('c.deletedAt IS NULL')
            ->andWhere('c.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', TrainingCourse::STATUS_PUBLISHED)
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
            ->setParameter('status', TrainingCourse::STATUS_PUBLISHED)
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
            ->andWhere('entity.status = :status')
            ->andWhere('entity.deletedAt IS NULL')
            ->setParameter('status', TrainingCourse::STATUS_PUBLISHED)
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return array
     */
    public function getAllCourses(): array
    {
        return $this->createQueryBuilder('tc')
            ->where('tc.deletedAt IS NULL')
            ->andWhere('tc.status = :status')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->getQuery()
            ->getResult();
    }
}