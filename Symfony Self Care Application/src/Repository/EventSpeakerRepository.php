<?php

namespace App\Repository;

use App\Entity\EventSpeaker;
use App\Entity\Language;
use App\Entity\User;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventSpeaker>
 *
 * @method EventSpeaker|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventSpeaker|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventSpeaker[]    findAll()
 * @method EventSpeaker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventSpeakerRepository extends ServiceEntityRepository
{
    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    public function __construct(ManagerRegistry $registry, DefaultHelper $helper)
    {
        parent::__construct($registry, EventSpeaker::class);
        $this->helper = $helper;
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countSpeakers(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('es')
            ->select('COUNT(es.id)')
            ->where('es.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @return array
     */
    public function findSpeakersByFilters(string $column, string $dir, $keyword): array
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('es')
            ->select("
                es.id,
                es.uuid,
                es.name,
                es.surname,
                es.role,
                es.company,
                COALESCE(es.fileName, '$defaultImage') as fileName,
                es.status,
                DATE_FORMAT(es.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('es.deletedAt IS NULL');

        // Field search
        $fields = [
            'es.id',
            'es.name',
            'es.surname',
            'es.company',
            'es.role'
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
            ->orderBy('es.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }
}
