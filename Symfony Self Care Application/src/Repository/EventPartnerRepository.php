<?php

namespace App\Repository;

use App\Entity\EventPartner;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventPartner>
 *
 * @method EventPartner|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventPartner|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventPartner[]    findAll()
 * @method EventPartner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventPartnerRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventPartner::class);
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countPartners(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('ep')
            ->select('COUNT(ep.id)')
            ->where('ep.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @return array
     */
    public function findPartnersByFilters(string $column, string $dir, $keyword): array
    {
        $queryBuilder = $this->createQueryBuilder('ep')
            ->select("
                ep.id,
                ep.uuid,
                ep.name,
                ep.type,
                ep.fileName,
                DATE_FORMAT(ep.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('ep.deletedAt IS NULL');

        // Field search
        $fields = [
            'ep.id',
            'ep.name',
            'ep.type'
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
            ->orderBy('ep.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }
}
