<?php

namespace App\Repository;

use App\Entity\PacientClinicalExam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientClinicalExam>
 *
 * @method PacientClinicalExam|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientClinicalExam|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientClinicalExam[]    findAll()
 * @method PacientClinicalExam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientClinicalExamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientClinicalExam::class);
    }

    public function add(PacientClinicalExam $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientClinicalExam $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function findDataForDatatable($pacient) {
        $data = $this->createQueryBuilder('ce')
                ->select('ce.objectiveExamination, ce.generalState, ce.nutritionalStatus, ce.stateOfConsciousness, ce.uid, ce.createdAt, u.firstName, u.lastName')
                ->join('ce.addedBy', 'u')
                ->where('ce.pacient = :pacient')
                ->andWhere('ce.deletedAt IS NULL')
                ->setParameter('pacient', $pacient)
                ->getQuery()
                ->getArrayResult()
        ;

        return array_map(function ($item) {
            // reformat datetime
            $item['createdAt'] = $item['createdAt']->format('d-m-Y');
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

}
