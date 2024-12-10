<?php

namespace App\Repository;

use App\Entity\Summary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Summary>
 *
 * @method Summary|null find($id, $lockMode = null, $lockVersion = null)
 * @method Summary|null findOneBy(array $criteria, array $orderBy = null)
 * @method Summary[]    findAll()
 * @method Summary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummaryRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Summary::class);
    }

    public function add(Summary $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Summary $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function findSummaryDataForReport($user, $type, $month, $status = null) {
        $queryBuilder = $this->createQueryBuilder('s')
                ->select("s.id, COUNT(sp.id) AS pacientsCount, DATE_FORMAT(s.summaryDate, '%d') AS day")
                ->leftJoin('s.summaryPacients', 'sp')
                ->where('s.user = :user')
                ->andWhere('s.type = :type')
                ->andWhere("DATE_FORMAT(s.summaryDate, '%m-%Y') = :month")
                ->setParameter('user', $user)
                ->setParameter('type', $type)
                ->setParameter('month', $month)
                ->groupBy('s.id');
                
        if (null !== $status) {
            $queryBuilder->andWhere('sp.status = :status')
                    ->setParameter('status', $status);
        }
        
        return $queryBuilder->getQuery()
                ->getArrayResult();
    }

}
