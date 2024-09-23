<?php

namespace App\Repository;

use App\Entity\Education;
use App\Entity\Language;
use App\Helper\LanguageHelper;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Education>
 *
 * @method Education|null find($id, $lockMode = null, $lockVersion = null)
 * @method Education|null findOneBy(array $criteria, array $orderBy = null)
 * @method Education[]    findAll()
 * @method Education[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Education::class);
    }

    public function getAllCourseLocations($type): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('DISTINCT c.name')
            ->join('e.county', 'c')
            ->where('e.deletedAt IS NULL');

        if (null !== $type && $type !== 'all') {
            $qb->where('e.type = :type')
                ->setParameter('type', $type);
        }

        $results = $qb->getQuery()->getScalarResult();

        return array_map(function ($result) {
            return $result['name'];
        }, $results);
    }

    public function findTotalCount($type = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.deletedAt IS NULL');

        if (null !== $type) {
            $qb->andWhere('e.type = :type')
                ->setParameter('type', $type);
        }

        return
            $qb->getQuery()
                ->getSingleScalarResult();
    }

    public function findCoursesByFilters($language, $type = "all", $location = "all", $category = 'all', $query = null, $limit = 3, $offset = 0)
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.educationTranslations', 'et')
            ->join('e.county', 'c')
            ->where('e.deletedAt IS NULL')
            ->andWhere('et.language = :language')
            ->setParameter('language', $language);

        if ($query) {
            if (null !== $category && $category !== 'all') {
                $qb->join('e.category', 'cat')
                    ->join('e.certification', 'certification')
                    ->andWhere('cat.slug = :category')
                    ->orWhere('certification.slug = :category')
                    ->setParameter('category', $category);
            }

            return $qb->andWhere('et.title LIKE :query OR et.description LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();
        }

        if (null !== $type && $type !== 'all') {
            $qb->andWhere('e.type = :type')
                ->setParameter('type', $type);
        }

        if (null !== $location && $location !== 'all') {
            $qb->andWhere('c.name = :location')
                ->setParameter('location', $location);
        }

        if (null !== $category && $category !== 'all') {
            $qb->join('e.category', 'cat')
                ->join('e.certification', 'certification')
                ->andWhere('cat.slug = :category')
                ->orWhere('certification.slug = :category')
                ->setParameter('category', $category);
        }

        return $qb
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function searchEducation(string $query, Language $language, int $limit = 4, int $offset = 0, bool $count = false)
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.educationTranslations', 'et')
            ->where('e.deletedAt IS NULL')
            ->andWhere('et.language = :language')
            ->andWhere('et.title LIKE :query OR et.description LIKE :query')
            ->setParameter('language', $language)
            ->setParameter('query', '%' . $query . '%');

        if ($count) {
            return $qb
                ->setMaxResults($limit)
                ->select('COUNT(e.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $qb->select("
                e.id,
                e.imageName,
                e.type,
                e.location,
                e.slug,
                et.title,
                et.shortDescription,
                e.endDate,
                e.startDate")
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }


    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param string $type
     * @param Language $language
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     * @return array
     */
    public function findByFilters(string $column, string $dir, $keyword, string $type, Language $language, DateTimeInterface $startDate = null, DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->join('e.educationTranslations', 'et')
            ->select("
                e.id,
                e.uuid,
                e.imageName,
                et.title,
                e.price,
                e.type,
                e.vat,
                DATE_FORMAT(e.startDate, '%d-%m-%Y %H:%i') AS startDate,
                DATE_FORMAT(e.endDate, '%d-%m-%Y %H:%i') AS endDate"
            )
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.type = :type')
            ->andWhere('et.language = :language')
            ->setParameter('type', $type)
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'e.id',
            'et.title',
            'e.price',
            'e.vat',
            'e.startDate',
            'e.endDate',
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
                ->andWhere('e.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy($column === 'title' ? 'et.' . $column : 'e.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    public function getDataByMonthAndYear(int $year, string $type = Education::TYPE_COURSE): mixed
    {
        return $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count')
            ->where('YEAR(entity.createdAt) = :year')
            ->andWhere('entity.type = :type')
            ->andWhere('entity.deletedAt IS NULL')
            ->setParameter('type', $type)
            ->setParameter('year', $year)
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()
            ->getResult();

    }

    public function getDataByEducation($educationId, string $type = Education::TYPE_COURSE): mixed
    {
        $queryBuilder = $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, entity.price as price')
            ->andWhere('entity.type = :type')
            ->andWhere('entity.deletedAt IS NULL');

        if ($educationId !== 'all') {
            $queryBuilder->andWhere('entity.id = :educationId')
                ->setParameter('educationId', $educationId);
        }

        return $queryBuilder
            ->setParameter('type', $type)
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
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function getTeamMemberEducationsCount($user, $startDate = null, $endDate = null)
    {
        $qb = $this->createQueryBuilder('entity')
            ->join('entity.teamMembers', 'teamMembers')
            ->select('COUNT(DISTINCT teamMembers.id)')
            ->where('teamMembers.id = :user')
            ->setParameter(':user', $user);

        if ($startDate !== null && $endDate !== null) {
            $qb
                ->andWhere('entity.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $qb->getQuery()
            ->getSingleColumnResult();
    }
}
