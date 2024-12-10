<?php

namespace App\Repository;

use App\Entity\NursingHomeRoom;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NursingHomeRoom>
 *
 * @method NursingHomeRoom|null find($id, $lockMode = null, $lockVersion = null)
 * @method NursingHomeRoom|null findOneBy(array $criteria, array $orderBy = null)
 * @method NursingHomeRoom[]    findAll()
 * @method NursingHomeRoom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NursingHomeRoomRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingHomeRoom::class);
    }

    public function add(NursingHomeRoom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NursingHomeRoom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRoomsByNursingHome($nursingHome)
    {
        return $this->createQueryBuilder('nh')
            ->select('nh.id, nh.roomNumber')
            ->orderBy('nh.roomNumber', 'ASC')
            ->where('nh.nursingHome = :nursingHome')
            ->setParameter('nursingHome', $nursingHome)
            ->getQuery()
            ->getArrayResult();
    }

    public function findFloorsByNursingHome($nursingHome)
    {
        $floors = $this->createQueryBuilder('nh')
            ->select('DISTINCT(nh.floor)')
            ->orderBy('nh.floor', 'ASC')
            ->where('nh.nursingHome = :nursingHome')
            ->setParameter('nursingHome', $nursingHome)
            ->getQuery()
            ->getArrayResult();

        return array_merge(...$floors);
    }

    public function findRoomsList($nursingHome = null)
    {
        $qb = $this->createQueryBuilder('nhr')
            ->orderBy('nhr.roomNumber', 'ASC');

        if (null !== $nursingHome) {
            $qb->where('nhr.nursingHome = :nursingHome')
                ->setParameter('nursingHome', $nursingHome);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function findFloorsList(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('nhr')
            ->join('nhr.nursingHome', 'nh')
            ->select('DISTINCT(nhr.floor)');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->where('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->where('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        $floors = $queryBuilder
            ->orderBy('nhr.floor', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_merge(...$floors);
    }

    public function findRoomsByNursingHomeAndFloors($nursingHome, $floors)
    {
        return $this->createQueryBuilder('nhr')
            ->select('nhr.id, nhr.roomNumber')
            ->where('nhr.nursingHome = :nursingHome')
            ->andWhere('nhr.floor IN (:floors)')
            ->setParameter('nursingHome', $nursingHome)
            ->setParameter('floors', $floors)
            ->orderBy('nhr.roomNumber')
            ->getQuery()
            ->getArrayResult();
    }

}
