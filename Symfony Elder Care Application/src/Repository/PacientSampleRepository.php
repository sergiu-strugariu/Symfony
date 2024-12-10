<?php

namespace App\Repository;

use App\Entity\PacientSample;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientSample>
 *
 * @method PacientSample|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientSample|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientSample[]    findAll()
 * @method PacientSample[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientSampleRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientSample::class);
    }

    public function add(PacientSample $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientSample $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSamplesByPacientAndInterval($pacientId, $startDate, $endDate)
    {
        return $this->createQueryBuilder('ps')
            ->select("ps.uid, ps.description, ps.status, ps.comment, ps.contactedFamily, ps.collected")
            ->where('ps.pacient = :pacientId')
            ->andWhere('ps.sampleDate BETWEEN :startDate AND :endDate')
            ->setParameter('pacientId', $pacientId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('ps.sampleDate', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @param null $date
     * @param null $nursingHome
     * @return float|int|mixed|string
     */
    public function findSamples(User $user, $date = null, $nursingHome = null)
    {
        $qb = $this->createQueryBuilder('ps')
            ->where('ps.id > 0')
            ->join('ps.pacient', 'p')
            ->join('p.nursingHome', 'nh');

        if ($user->hasRole('ROLE_ADMIN')) {
            $qb
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $qb
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if (!empty($nursingHome)) {
            $qb
                ->andWhere('nh.id = :id')
                ->setParameter('id', $nursingHome);
        }

        if (!empty($date)) {
            $qb
                ->andWhere("DATE_FORMAT(ps.sampleDate, '%Y-%m-%d') = :date")
                ->setParameter('date', $date);
        }

        return $qb
            ->orderBy('ps.sampleDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
