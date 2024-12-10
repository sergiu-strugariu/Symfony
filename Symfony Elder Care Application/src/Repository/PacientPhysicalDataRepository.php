<?php

namespace App\Repository;

use App\Entity\PacientPhysicalData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientPhysicalData>
 *
 * @method PacientPhysicalData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientPhysicalData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientPhysicalData[]    findAll()
 * @method PacientPhysicalData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientPhysicalDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientPhysicalData::class);
    }

    public function add(PacientPhysicalData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientPhysicalData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function findPhysicalDataByPacientAndInterval($pacientId, $startDate, $endDate) {
        return $this->createQueryBuilder('pd')
                        ->select("pd.massage, pd.therapeuticMassage, pd.tappingMassage, pd.bodyRepositioningImmobilizedPatients, pd.correctingBodyPostureAndAlignment, pd.increasingBodyCoordinationAndBalance, pd.increasingJointMobility, pd.increasingJointMobilityPassive, pd.increasingJointMobilityActive, pd.increasingJointMobilityPassiveActive, pd.increasingJointMobilityActiveVoluntary, pd.increasingJointMobilityAutoPassive, pd.increasingMuscleStrengthAndEndurance, pd.scriptotherapy, pd.rocherCage, pd.multifunctionalDevice, pd.stretching, pd.walkingExercises, pd.walkingExercisesSteps, pd.walkingExercisesSupport, pd.walkingExercisesBicycle, pd.walkingExercisesWalkingLane, pd.groupExercises, pd.groupExercisesJointMobility, pd.groupExercisesBodyBalanceAndCoordination, pd.groupExercisesResistanceAndMuscleStrength, pd.trellisExercises, pd.refusal, pd.medicalProblem, pd.observations")
                        ->where('pd.pacient = :pacientId')
                        ->andWhere('pd.date BETWEEN :startDate AND :endDate')
                        ->setParameter('pacientId', $pacientId)
                        ->setParameter('startDate', $startDate)
                        ->setParameter('endDate', $endDate)
                        ->orderBy('pd.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }
    
    public function findPhysicalDataByPacientAndMonth($pacient, $month) {
        return $this->createQueryBuilder('pd')
                        ->select("pd.massage, pd.therapeuticMassage, pd.tappingMassage, pd.bodyRepositioningImmobilizedPatients, pd.correctingBodyPostureAndAlignment, pd.increasingBodyCoordinationAndBalance, pd.increasingJointMobility, pd.increasingJointMobilityPassive, pd.increasingJointMobilityActive, pd.increasingJointMobilityPassiveActive, pd.increasingJointMobilityActiveVoluntary, pd.increasingJointMobilityAutoPassive, pd.increasingMuscleStrengthAndEndurance, pd.scriptotherapy, pd.rocherCage, pd.multifunctionalDevice, pd.stretching, pd.walkingExercises, pd.walkingExercisesSteps, pd.walkingExercisesSupport, pd.walkingExercisesBicycle, pd.walkingExercisesWalkingLane, pd.groupExercises, pd.groupExercisesJointMobility, pd.groupExercisesBodyBalanceAndCoordination, pd.groupExercisesResistanceAndMuscleStrength, pd.trellisExercises, pd.refusal, pd.medicalProblem, pd.observations, DATE_FORMAT(pd.date, '%e') AS day, DATE_FORMAT(pd.createdAt, '%d-%m-%Y %H:%i') AS createdAt")
                        ->where('pd.pacient = :pacient')
                        ->andWhere("DATE_FORMAT(pd.date, '%m-%Y') = :month")
                        ->setParameter('pacient', $pacient)
                        ->setParameter('month', $month)
                        ->orderBy('pd.date', 'ASC')
                        ->getQuery()
                        ->getArrayResult();
    }

}
