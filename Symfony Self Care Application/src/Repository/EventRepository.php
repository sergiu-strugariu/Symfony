<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Language;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countEvents(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('ev')
            ->select('COUNT(ev.id)')
            ->where('ev.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param Language $language
     * @return array
     */
    public function findEventsByFilters(string $column, string $dir, $keyword, Language $language): array
    {
        $queryBuilder = $this->createQueryBuilder('ev')
            ->join('ev.eventTranslations', 'tr')
            ->select("
                ev.id,
                ev.uuid,
                ev.slug,
                tr.title,
                ev.fileName,
                ev.status,
                ev.eventStatus,
                DATE_FORMAT(ev.startDate, '%d-%m-%Y %H:%i') as startDate,
                DATE_FORMAT(ev.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('ev.deletedAt IS NULL')
            ->andWhere('tr.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'ev.id',
            'tr.title',
            'ev.status',
            'ev.slug'
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
            ->orderBy($column === 'title' ? 'tr.' . $column : 'ev.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function getSingleData(string $slug): mixed
    {
        return $this->createQueryBuilder('ev')
            ->where('ev.slug = :slug')
            ->andWhere('ev.deletedAt IS NULL')
            ->andWhere('ev.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getYears(): array
    {
        return $this->createQueryBuilder('ev')
            ->select('DISTINCT YEAR(ev.startDate) as year')
            ->andWhere('ev.deletedAt IS NULL')
            ->andWhere('ev.status = :status')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @param string $status
     * @param string $year
     * @param Language $language
     * @param string $sort
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return mixed
     */
    public function getEventsByFilters(Language $language, string $sort = 'id', string $order = 'DESC', int $limit = 10, int $offset = 0, bool $isCount = false, string $status = '', string $year = ''): mixed
    {
        $queryBuilder = $this->createQueryBuilder('ev')
            ->leftJoin('ev.eventTranslations', 'tr')
            ->leftJoin('ev.county', 'cty')
            ->leftJoin('ev.city', 'ciy')
            ->where('ev.deletedAt IS NULL')
            ->andWhere('ev.status = :status')
            ->andWhere('tr.language = :language')
            ->setParameter('language', $language)
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED);

        // Filter by @eventStatus
        if (!empty($status)) {
            $queryBuilder
                ->andWhere('ev.eventStatus = :statusEvent')
                ->setParameter('statusEvent', $status);
        }

        // Filter by @year
        if (!empty($year)) {
            $queryBuilder
                ->andWhere('YEAR(ev.startDate) = :year')
                ->setParameter('year', $year);
        }

        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT ev.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $queryBuilder
            ->select("
                ev.id,
                ev.uuid,
                ev.slug,
                ev.fileName,
                ev.address,
                ev.eventStatus,
                cty.name as county,
                ciy.name as city,
                tr.title,
                tr.shortDescription,
                DATE_FORMAT(ev.startDate, '%d/%m/%Y') as startDate,
                DATE_FORMAT(ev.startDate, '%H:%i') as timeAt")
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->groupBy('ev.id')
            ->orderBy("ev.$sort", $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getAllEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.status = :status')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->getQuery()
            ->getResult();
    }
}
