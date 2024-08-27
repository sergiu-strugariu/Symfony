<?php

namespace App\Repository;

use App\Entity\Feedback;
use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feedback>
 *
 * @method Feedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feedback[]    findAll()
 * @method Feedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }
    
    /**
     * @return float|bool|int|string|null
     */
    public function findTotalCount(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.status = :status')
            ->setParameter('status', Feedback::STATUS_COMPLETED)
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
    public function findByFilters(string $column, string $dir, $keyword, Language $language): array
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->join('f.education', 'e')
            ->join('e.educationTranslations', 'et')
            ->select("
                f.id,
                f.uuid,
                u.firstName,
                et.title,
                DATE_FORMAT(f.answeredAt, '%d-%m-%Y %H:%i') as answeredAt"
            )
            ->where('f.status = :status')
            ->andWhere('et.language = :language')
            ->setParameter('status', Feedback::STATUS_COMPLETED)
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'f.id',
            'u.firstName',
            'et.title'
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
            ->orderBy('f.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

}
