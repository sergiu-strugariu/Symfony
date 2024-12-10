<?php

namespace App\Repository;

use App\Entity\PacientVisitCalendar;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientVisitCalendar>
 *
 * @method PacientVisitCalendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientVisitCalendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientVisitCalendar[]    findAll()
 * @method PacientVisitCalendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientVisitCalendarRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientVisitCalendar::class);
    }

    public function add(PacientVisitCalendar $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientVisitCalendar $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user
     * @param bool $isMedic
     * @param string $filterType
     * @return array
     */
    public function findVisitsForDatatable(User $user, bool $isMedic = false, string $filterType = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->select("DATE_FORMAT(v.startDate, '%d-%m-%Y') as date, (CASE WHEN v.pacient IS NULL THEN pp.relationName ELSE CONCAT(p.firstName, ' ', p.lastName) END) AS name, DATE_FORMAT(v.startDate, '%H:%i') as hour, v.observations, v.status")
            ->leftJoin('v.nursingHome', 'nh')
            ->leftJoin('v.pacient', 'p')
            ->leftJoin('v.prospect', 'pp')
            ->where('v.id IS NOT NULL');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.user = :user')
                ->setParameter('user', $user);
        } else {
            $queryBuilder
                ->andWhere('nh.id = :nursingHome')
                ->setParameter('nursingHome', $user->getNursingHome());
        }

        if ($filterType !== '') {
            $queryBuilder
                ->andWhere('v.type = :type')
                ->setParameter('type', $filterType);
        }

        if ($isMedic) {
            $queryBuilder
                ->andWhere('v.type =:type')
                ->setParameter('type', PacientVisitCalendar::VISIT_TYPE_MEDICAL);
        }

        $data = $queryBuilder
            ->orderBy('v.startDate', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat datetime
            $item['relation'] = '-';

            return $item;
        }, $data);
    }

    /**
     * @param bool $showAdmittedOnly
     * @param $nursingHomeUuid
     * @param $startDate
     * @param $endDate
     * @return float|int|array|string
     */
    public function findVisitsByDateAndNursingHome(bool $showAdmittedOnly, $nursingHomeUuid, $startDate, $endDate)
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->select("v.uid AS id, (CASE WHEN v.pacient IS NULL THEN pp.relationName ELSE CONCAT(p.firstName, ' ', p.lastName) END) AS title, (CASE WHEN v.status = :canceled THEN 'red' WHEN (v.type = :type_relation AND v.observations IS NOT NULL) THEN 'black' WHEN v.type = :type_medical THEN 'gray' WHEN v.type = :type_relation THEN 'blue' ELSE 'green' END) AS color, (CASE WHEN v.type = :type_relation THEN true ELSE false END) AS editable, p.uid AS userUuid, DATE_FORMAT(v.startDate, '%Y-%m-%dT%H:%i:%s') AS start, DATE_FORMAT(v.endDate, '%Y-%m-%dT%H:%i:%s') AS end, v.observations AS description, nh.name AS location")
            ->join('v.nursingHome', 'nh')
            ->leftJoin('v.pacient', 'p')
            ->leftJoin('v.prospect', 'pp')
            ->where('nh.uid = :nursingHomeUuid')
            ->andWhere('v.startDate BETWEEN :startDate AND :endDate')
            ->setParameter('nursingHomeUuid', $nursingHomeUuid)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('type_relation', PacientVisitCalendar::VISIT_TYPE_RELATION)
            ->setParameter('type_medical', PacientVisitCalendar::VISIT_TYPE_MEDICAL)
            ->setParameter('canceled', PacientVisitCalendar::VISIT_STATUS_CANCELED);

        if ($showAdmittedOnly) {
            $queryBuilder
                ->andWhere('v.type =:type')
                ->setParameter('type', PacientVisitCalendar::VISIT_TYPE_MEDICAL);
        }

        return $queryBuilder
            ->getQuery()
            ->getArrayResult();
    }

    public function findLatestVisitByPacient($pacientUuid)
    {
        return $this->createQueryBuilder('v')
            ->join('v.pacient', 'p')
            ->where('p.uid = :pacientUuid')
            ->andWhere('v.startDate <= :currentDate')
            ->setParameter('pacientUuid', $pacientUuid)
            ->setParameter('currentDate', new \DateTime())
            ->orderBy('v.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findVisitsByPacient($pacient, $date = null)
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->where('v.pacient = :pacient')
            ->setParameter('pacient', $pacient)
            ->orderBy('v.startDate', 'DESC');

        if (null !== $date) {
            $queryBuilder->andWhere("DATE_FORMAT(v.startDate, '%Y-%m') = :date")
                ->setParameter('date', $date);
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }

}
