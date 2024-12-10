<?php

namespace App\Repository;

use App\Entity\PacientMedicalData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientMedicalData>
 *
 * @method PacientMedicalData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientMedicalData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientMedicalData[]    findAll()
 * @method PacientMedicalData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientMedicalDataRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientMedicalData::class);
    }

    public function add(PacientMedicalData $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientMedicalData $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDataForDatatable($pacient) {
        $data = $this->createQueryBuilder('md')
                ->select('md.bloodType, md.rh, md.vaccinatedAgainstCovid, md.hadCovid, md.weight, md.height, md.uid, md.createdAt, u.firstName, u.lastName')
                ->join('md.addedBy', 'u')
                ->where('md.pacient = :pacient')
                ->andWhere('md.deletedAt IS NULL')
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
