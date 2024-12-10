<?php

namespace App\Repository;

use App\Entity\PacientGeneralData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientGeneralData>
 *
 * @method PacientGeneralData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientGeneralData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientGeneralData[]    findAll()
 * @method PacientGeneralData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientGeneralDataRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientGeneralData::class);
    }

    public function add(PacientGeneralData $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientGeneralData $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDataForDatatable($pacient) {
        $data = $this->createQueryBuilder('gd')
                ->select('gd.createdAt, gd.pacientType, gd.pacientMobility, gd.diet, gd.requiresDiapers, gd.requiresMedicalBed, gd.nosocomialInfection, gd.uid, u.firstName, u.lastName')
                ->join('gd.addedBy', 'u')
                ->where('gd.pacient = :pacient')
                ->andWhere('gd.deletedAt IS NULL')
                ->setParameter('pacient', $pacient)
                ->getQuery()
                ->getArrayResult()
        ;

        return array_map(function ($item) {
            // reformat datetime
            $item['createdAt'] = ($item['createdAt'] instanceof \DateTime) ? $item['createdAt']->format('d-m-Y') : '-';
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    public function findLatestGeneralData($pacient) {
        return $this->createQueryBuilder('gd')
                        ->where('gd.pacient = :pacient')
                        ->andWhere('gd.deletedAt IS NULL')
                        ->setParameter('pacient', $pacient)
                        ->orderBy('gd.createdAt', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

}
