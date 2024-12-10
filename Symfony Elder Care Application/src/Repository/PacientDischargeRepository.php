<?php

namespace App\Repository;

use App\Entity\PacientDischarge;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientDischarge>
 *
 * @method PacientDischarge|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientDischarge|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientDischarge[]    findAll()
 * @method PacientDischarge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientDischargeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientDischarge::class);
    }

    public function add(PacientDischarge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientDischarge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param null $pacient
     * @param User $user
     * @return array
     */
    public function findDataForDatatable($pacient = null, User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('d.dischargeDate, d.dischargeReason, d.clinicalDiagnostic, d.paraclinicalDiagnostic, d.epicrisis, d.uid, p.uid AS pacientUuid, p.firstName AS pacientFirstName, p.lastName AS pacientLastName, u.firstName AS dischargedByFirstName, u.lastName AS dischargedByLastName')
            ->join('d.pacient', 'p')
            ->join('d.dischargedBy', 'u')
            ->join('p.nursingHome', 'nh')
            ->where('d.deletedAt IS NULL');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if (null !== $pacient) {
            $queryBuilder
                ->andWhere('d.pacient = :pacient')
                ->setParameter('pacient', $pacient);
        }

        $data = $queryBuilder
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat datetime
            $item['dischargeDate'] = ($item['dischargeDate'] instanceof \DateTime) ? $item['dischargeDate']->format('d-m-Y') : '-';
            $item['name'] = (empty($item['pacientFirstName']) && empty($item['pacientLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['pacientFirstName'], $item['pacientLastName']);
            $item['dischargedBy'] = (empty($item['dischargedBFirstName']) && empty($item['dischargedByLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['dischargedByFirstName'], $item['dischargedByLastName']);

            return $item;
        }, $data);
    }

    public function findPacientLatestDischargeData($pacientId)
    {
        $data = $this->createQueryBuilder('pd')
            ->select("pd.dischargeDate, DATE_FORMAT(pd.dischargeDate, '%d/%m/%Y') as dischargeDateFormatted, pd.dischargeReason")
            ->join('pd.pacient', 'p')
            ->where("p.id = :pacientId")
            ->setParameter('pacientId', $pacientId)
            ->setMaxResults(1)
            ->orderBy('pd.dischargeDate', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return reset($data);
    }

}
