<?php

namespace App\Repository;

use App\Entity\NursingHome;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NursingHome>
 *
 * @method NursingHome|null find($id, $lockMode = null, $lockVersion = null)
 * @method NursingHome|null findOneBy(array $criteria, array $orderBy = null)
 * @method NursingHome[]    findAll()
 * @method NursingHome[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NursingHomeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingHome::class);
    }

    public function add(NursingHome $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NursingHome $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user
     * @return float|int|array|string
     */
    public function findNursingHomesForDatatable(User $user)
    {
        $queryBuilder = $this
            ->createQueryBuilder('nh')
            ->leftJoin('nh.county', 'cn')
            ->leftJoin('nh.city', 'ct')
            ->select("nh.name, nh.officialName, nh.cui, nh.address, nh.uid, cn.name as county, ct.name as city, DATE_FORMAT(nh.createdAt, '%d-%m-%Y') as createdAt");

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->where('nh.user = :user')
                ->setParameter('user', $user);
        } else {
            $queryBuilder
                ->where('nh.id = :nursingHome')
                ->setParameter('nursingHome', $user->getNursingHome());
        }

        return $queryBuilder
            ->orderBy('nh.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $user
     * @return array|array[]
     */
    public function findNursingHomesByUser($user): array
    {
        if ($user->hasRole('ROLE_ADMIN')) {
            return $this->createQueryBuilder('nh')
                ->select('nh.uid, nh.name')
                ->where('nh.user = :user')
                ->setParameter('user', $user)
                ->orderBy('nh.name', 'ASC')
                ->getQuery()
                ->getArrayResult();
        }

        $nursingHome = $user->getNursingHome();

        if (null !== $nursingHome) {
            return [
                [
                    'uid' => $nursingHome->getUid(),
                    'name' => $nursingHome->getName()
                ]
            ];
        }

        return [];
    }

    public function countNursingHomes($user = null): int
    {
        $qb = $this->createQueryBuilder('nh')
            ->select('COUNT(nh.id)');

        if ($user !== null && $user->hasRole('ROLE_ADMIN')) {
            $qb->where('nh.user = :user')
                ->setParameter('user', $user);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function getTotalUserCount(User $user = null)
    {
        $qb = $this->createQueryBuilder('nh')
            ->select('COUNT(u.id) as totalUserCount')
            ->leftJoin('nh.users', 'u');

        if ($user && $user->hasRole('ROLE_ADMIN')) {
            $qb->where('nh.user = :user')
                ->setParameter('user', $user);
        }

        return $qb->getQuery()
            ->getSingleScalarResult();
    }
}
