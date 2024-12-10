<?php

namespace App\Repository;

use App\Entity\PacientOrderlyData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientOrderlyData>
 *
 * @method PacientOrderlyData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientOrderlyData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientOrderlyData[]    findAll()
 * @method PacientOrderlyData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientOrderlyDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientOrderlyData::class);
    }

    public function add(PacientOrderlyData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientOrderlyData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function findOrderlyDataByPacientAndInterval($pacientId, $startDate, $endDate) {
        return $this->createQueryBuilder('po')
                        ->select("po.hydrationFood, po.diuresis, po.stool, po.bathing, po.diapers, po.observations")
                        ->where('po.pacient = :pacientId')
                        ->andWhere('po.date BETWEEN :startDate AND :endDate')
                        ->setParameter('pacientId', $pacientId)
                        ->setParameter('startDate', $startDate)
                        ->setParameter('endDate', $endDate)
                        ->orderBy('po.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }
    
     public function findOrderlyDataByPacientAndMonth($pacient, $month) {
        return $this->createQueryBuilder('po')
                        ->select("po.hydrationFood, po.diuresis, po.stool, po.bathing, po.diapers, po.observations, DATE_FORMAT(po.date, '%e') AS day, DATE_FORMAT(po.date, '%H:%i') AS hour")
                        ->where('po.pacient = :pacient')
                        ->andWhere("DATE_FORMAT(po.date, '%m-%Y') = :month")
                        ->setParameter('pacient', $pacient)
                        ->setParameter('month', $month)
                        ->orderBy('po.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

}
