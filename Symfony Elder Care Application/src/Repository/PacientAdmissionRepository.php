<?php

namespace App\Repository;

use App\Entity\PacientAdmission;
use App\Entity\Pacient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientAdmission>
 *
 * @method PacientAdmission|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientAdmission|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientAdmission[]    findAll()
 * @method PacientAdmission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientAdmissionRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientAdmission::class);
    }

    public function add(PacientAdmission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientAdmission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user
     * @return array
     */
    public function findDataForDatatable(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('pa')
            ->select('pa.admissionDate, pa.pacientType, pa.pacientMobility, pa.diet, pa.requiresDiapers, pa.requiresMedicalBed, pa.nosocomialInfection, pa.uid, p.firstName AS pacientFirstName, p.lastName AS pacientLastName, u.firstName AS admittedByFirstName, u.lastName AS admittedByLastName')
            ->join('pa.pacient', 'p')
            ->join('pa.admittedBy', 'u')
            ->join('p.nursingHome', 'nh');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->where('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->where('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        $data = $queryBuilder
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat datetime
            $item['admissionDate'] = ($item['admissionDate'] instanceof \DateTime) ? $item['admissionDate']->format('d-m-Y') : '-';
            $item['name'] = (empty($item['pacientFirstName']) && empty($item['pacientLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['pacientFirstName'], $item['pacientLastName']);
            $item['medic'] = (empty($item['admittedByFirstName']) && empty($item['admittedByLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['admittedByFirstName'], $item['admittedByLastName']);

            return $item;
        }, $data);
    }

    public function findPacientAdmissions()
    {
        return $this->createQueryBuilder('pa')
            ->join('pa.pacient', 'p')
            ->where('p.status = :status')
            ->setParameter('status', Pacient::STATUS_ADMITTED)
            ->getQuery()
            ->getResult();
    }

    public function findPacientLatestAdmissionData($pacientId)
    {
        $data = $this->createQueryBuilder('pa')
            ->select("pa.admissionDate, DATE_FORMAT(pa.admissionDate, '%d/%m/%Y') as admissionDateFormatted")
            ->join('pa.pacient', 'p')
            ->where("p.id = :pacientId")
            ->setParameter('pacientId', $pacientId)
            ->setMaxResults(1)
            ->orderBy('pa.admissionDate', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return reset($data);
    }

}
