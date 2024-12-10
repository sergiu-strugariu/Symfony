<?php

namespace App\Repository;

use App\Entity\PacientDiagnosis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientDiagnosis>
 *
 * @method PacientDiagnosis|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientDiagnosis|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientDiagnosis[]    findAll()
 * @method PacientDiagnosis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientDiagnosisRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientDiagnosis::class);
    }

    public function add(PacientDiagnosis $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientDiagnosis $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDiagnosesByPacient($pacient, $returnArray = true) {
        $queryBuilder = $this->createQueryBuilder('pd')
                ->where('pd.pacient = :pacient')
                ->andWhere('pd.deletedAt IS NULL')
                ->setParameter('pacient', $pacient)
                ->orderBy('pd.createdAt', 'DESC');

        if ($returnArray) {
            $queryBuilder->select('pd.id, pd.diagnosis');
            return $queryBuilder->getQuery()
                            ->getArrayResult();
        }

        return $queryBuilder->getQuery()
                        ->getResult();
    }

}
