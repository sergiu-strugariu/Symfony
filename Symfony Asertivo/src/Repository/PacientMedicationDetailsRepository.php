<?php

namespace App\Repository;

use App\Entity\PacientMedicationDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientMedicationDetails>
 *
 * @method PacientMedicationDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientMedicationDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientMedicationDetails[]    findAll()
 * @method PacientMedicationDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientMedicationDetailsRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientMedicationDetails::class);
    }

    public function add(PacientMedicationDetails $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientMedicationDetails $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findTreatmentPlansByPacient($pacientId, $date = null, $dateFormat = '%m-%Y') {
        $queryBuilder = $this->createQueryBuilder('pmd')
                ->select("pmd.id, DATE_FORMAT(pmd.date, '%d/%m/%Y') as treatmentDate, DATE_FORMAT(pmd.date, '%H:%i') as treatmentHour, DATE_FORMAT(pmd.date, '%Y-%m-%d %H:%i') as treatmentDateFull, pmd.drug, pmd.dose, pmd.observations, pmd.status, a.firstName, a.lastName")
                ->join('pmd.pacient', 'p')
                ->leftJoin('pmd.assistant', 'a')
                ->where('p.id = :pacientId')
                ->setParameter('pacientId', $pacientId)
                ->orderBy('treatmentDate', 'ASC');

        if ($date !== null) {
            $queryBuilder->andWhere("DATE_FORMAT(pmd.date, '{$dateFormat}') = :date")
                    ->setParameter('date', $date);
        }

        return $queryBuilder->getQuery()
                        ->getArrayResult();
    }

    public function findTreatmentPlansByPacientForMedicalRecords($pacientId, $startDate = null, $endDate = null) {
        $queryBuilder = $this->createQueryBuilder('pmd')
                ->select("pmd.id AS planDetailsId, pm.id AS planId, DATE_FORMAT(pmd.date, '%d/%m/%Y') as treatmentDate, DATE_FORMAT(pmd.date, '%e') as treatmentDay, DATE_FORMAT(pmd.date, '%H') as treatmentHour, DATE_FORMAT(pmd.date, '%i') as treatmentMinute, pmd.drug, pmd.dose,  pmd.observations")
                ->join('pmd.pacient', 'p')
                ->join('pmd.pacientMedication', 'pm')
                ->where('p.id = :pacientId')
                ->setParameter('pacientId', $pacientId)
                ->orderBy('treatmentDate', 'ASC');

        if (null !== $startDate && null !== $endDate) {
            $queryBuilder->andWhere('pmd.date BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate);
        }

        return $queryBuilder->getQuery()
                        ->getArrayResult();
    }

    public function findNecessaryTreatmentPlansByPacient($pacientId) {
        return $this->createQueryBuilder('pmd')
                        ->select("pmd.drug, pmd.dose, pmd.observations")
                        ->join('pmd.pacient', 'p')
                        ->where('p.id = :pacientId')
                        ->andWhere("pmd.date IS NULL")
                        ->setParameter('pacientId', $pacientId)
                        ->getQuery()
                        ->getArrayResult();
    }

    public function findTreatmentPlansByPacientAndInterval($pacientId, $startDate, $endDate) {
        return $this->createQueryBuilder('pmd')
                        ->select("pmd.id, pmd.drug, pmd.dose, pmd.observations, pmd.status")
                        ->where('pmd.pacient = :pacientId')
                        ->andWhere('pmd.date BETWEEN :startDate AND :endDate')
                        ->setParameter('pacientId', $pacientId)
                        ->setParameter('startDate', $startDate)
                        ->setParameter('endDate', $endDate)
                        ->orderBy('pmd.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

}
