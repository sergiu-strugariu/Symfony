<?php

namespace App\Repository;

use App\Entity\PacientMonitoringMedical;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientMonitoringMedical>
 *
 * @method PacientMonitoringMedical|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientMonitoringMedical|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientMonitoringMedical[]    findAll()
 * @method PacientMonitoringMedical[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientMonitoringMedicalRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientMonitoringMedical::class);
    }

    public function add(PacientMonitoringMedical $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientMonitoringMedical $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findMonitoringMedicalByPacient($pacientId, $date = null, $dir = 'ASC') {
        $queryBuilder = $this->createQueryBuilder('pmm')
                ->select("pmm.id, u.firstName, u.lastName, pmm.temperature, pmm.saturation, pmm.systolicBloodPressure, pmm.diastolicBloodPressure, pmm.heartRate, pmm.glucose, pmm.infusion, pmm.observations, DATE_FORMAT(pmm.createdAt, '%H:%i') as monitoringHour, DATE_FORMAT(pmm.createdAt, '%d-%m-%Y %H:%i') as createdAt")
                ->join('pmm.pacient', 'p')
                ->join('pmm.addedBy', 'u')
                ->where('p.id = :pacientId')
                ->setParameter('pacientId', $pacientId);

        if ($date !== null) {
            $queryBuilder->andWhere("DATE_FORMAT(pmm.date, '%Y-%m-%d') = :date")
                    ->setParameter('date', $date);
        }
        
        $queryBuilder->orderBy('pmm.createdAt', $dir);

        return $queryBuilder->getQuery()
                        ->getArrayResult();
    }

    public function findMonitoringMedicalByPacientAndInterval($pacientId, $startDate, $endDate) {
        return $this->createQueryBuilder('pmm')
                        ->select("pmm.id, pmm.temperature, pmm.saturation, pmm.systolicBloodPressure, pmm.diastolicBloodPressure, pmm.heartRate, pmm.glucose, pmm.infusion, pmm.observations")
                        ->where('pmm.pacient = :pacientId')
                        ->andWhere('pmm.date BETWEEN :startDate AND :endDate')
                        ->setParameter('pacientId', $pacientId)
                        ->setParameter('startDate', $startDate)
                        ->setParameter('endDate', $endDate)
                        ->orderBy('pmm.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

    public function findMonitoringMedicalDataByPacientForChart($pacient, $type, $date) {
        $queryBuilder = $this->createQueryBuilder('pmm')
                ->where('pmm.pacient = :pacient')
                ->andWhere("pmm.date >= :date")
                ->setParameter('pacient', $pacient)
                ->setParameter('date', $date)
                ->orderBy('pmm.date', 'ASC');

        switch ($type) {
            case 'blood-pressure':
                $queryBuilder->select("pmm.systolicBloodPressure, pmm.diastolicBloodPressure, DATE_FORMAT(pmm.date, '%d-%m-%Y') AS date")
                        ->andWhere("pmm.systolicBloodPressure IS NOT NULL")
                        ->andWhere("pmm.diastolicBloodPressure IS NOT NULL");
                break;
            case 'saturation':
                $queryBuilder->select("pmm.saturation, DATE_FORMAT(pmm.date, '%d-%m-%Y') AS date")
                        ->andWhere("pmm.saturation IS NOT NULL");
                break;
            case 'heart-rate':
                $queryBuilder->select("pmm.heartRate, DATE_FORMAT(pmm.date, '%d-%m-%Y') AS date")
                        ->andWhere("pmm.heartRate IS NOT NULL");
                break;
            case 'glucose':
                $queryBuilder->select("pmm.glucose, DATE_FORMAT(pmm.date, '%d-%m-%Y') AS date")
                        ->andWhere("pmm.glucose IS NOT NULL");
                break;
            default:
                break;
        }

        return $queryBuilder->getQuery()
                        ->getArrayResult();
    }
    
    public function findMonitoringMedicalDataByPacientAndMonth($pacient, $month) {
        return $this->createQueryBuilder('pmd')
                        ->leftJoin('pmd.addedBy', 'user')
                        ->select("user.firstName, user.lastName, pmd.temperature, pmd.saturation, pmd.glucose, pmd.infusion, pmd.heartRate, pmd.systolicBloodPressure, pmd.diastolicBloodPressure, DATE_FORMAT(pmd.date, '%e') AS day, DATE_FORMAT(pmd.date, '%H:%i') AS hour")
                        ->where('pmd.pacient = :pacient')
                        ->andWhere("DATE_FORMAT(pmd.date, '%m-%Y') = :month")
                        ->setParameter('pacient', $pacient)
                        ->setParameter('month', $month)
                        ->orderBy('pmd.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

}
