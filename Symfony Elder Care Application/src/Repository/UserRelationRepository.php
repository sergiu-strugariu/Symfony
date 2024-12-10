<?php

namespace App\Repository;

use App\Entity\Pacient;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserRelation>
 *
 * @method UserRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRelation[]    findAll()
 * @method UserRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRelationRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRelation::class);
    }

    public function add(UserRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRelationsByUser($user)
    {
        $data = $this->createQueryBuilder('ur')
            ->select('u.firstName, u.lastName, u.email, u.phoneNumber, u.uid')
            ->leftJoin('ur.pacientRelation', 'u')
            ->where('ur.pacient = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat fields
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    public function findRelationsCountByUser($user)
    {
        return $this->createQueryBuilder('ur')
            ->select('COUNT(ur)')
            ->where('ur.pacient = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @param null $nursingHome
     * @return array
     */
    public function findRelationsForReport(User $user, $nursingHome = null): array
    {
        $queryBuilder = $this->createQueryBuilder('ur')
            ->select('u.id, u.uid AS relationUuid, u.firstName AS relationFirstName, u.lastName AS relationLastName, u.email, u.phoneNumber, p.id AS pacientId, p.firstName AS pacientFirstName, p.lastName AS pacientLastName, p.status, p.uid AS pacientUuid')
            ->join('ur.pacientRelation', 'u')
            ->join('ur.pacient', 'p')
            ->join('p.nursingHome', 'nh')
            ->where('u.id > 0');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if (!empty($nursingHome)) {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $nursingHome);
        }

        $data = $queryBuilder
            ->orderBy('u.lastName', 'ASC')
            ->groupBy('u.id')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat fields
            $item['relationName'] = (empty($item['relationFirstName']) && empty($item['relationLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['relationFirstName'], $item['relationLastName']);
            $item['pacientName'] = (empty($item['pacientFirstName']) && empty($item['pacientLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['pacientFirstName'], $item['pacientLastName']);
            $item['statusClass'] = Pacient::getPacientStatusClass($item['status']);

            return $item;
        }, $data);
    }

}
