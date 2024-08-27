<?php

namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gallery>
 *
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function getAllMultimediaTypes(): array
    {
        return
            $this->createQueryBuilder('e')
                ->select('DISTINCT e.type')
                ->getQuery()
                ->getArrayResult();
    }

    public function getAllMultimediaLocations($type): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('DISTINCT c.name')
            ->join('e.county', 'c');

        if (null !== $type && $type !== 'all') {
            $qb->where('e.type = :type')
                ->setParameter('type', $type);
        }

        $results = $qb->getQuery()->getScalarResult();

        return array_map(function ($result) {
            return $result['name'];
        }, $results);
    }

    public function findMultimediaByFilters($language, $type = "all", $location = "all", $query = null, $limit = 3, $offset = 0, $count = false)
    {
        $qb = $this->createQueryBuilder('m')
            ->join('m.county', 'c')
            ->where('m.status = :status')
            ->setParameter('status', Gallery::STATUS_PUBLISHED);

        if ($query) {
            $qb->andWhere('m.title LIKE :query')
                ->setParameter('query', '%' . $query . '%');

            if ($count) {
                $qb->select('COUNT(m.id)');
                return $qb->getQuery()->getSingleScalarResult();
            }

            $qb->setMaxResults($limit)
                ->setFirstResult($offset);

            return $qb->getQuery()->getResult();
        }

        if (null !== $type && $type !== 'all') {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $type);
        }

        if (null !== $location && $location !== 'all') {
            $qb->andWhere('c.name = :location')
                ->setParameter('location', $location);
        }

        if ($count) {
            $qb->select('COUNT(m.id)');
            return $qb->getQuery()->getSingleScalarResult();
        }

        return $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()->getResult();
    }


    public function findTotalCount($status = null): float|bool|int|string|null
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('COUNT(g.id)');

        if (null !== $status) {
            $queryBuilder->where('g.status = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select("
                g.id,
                g.uuid,
                g.status,
                g.title"
            )
            ->where('g.createdAt IS NOT NULL');

        // Field search
        $fields = [
            'g.id',
            'g.title',
            'g.status'
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
                ->andWhere('g.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('g.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

}
