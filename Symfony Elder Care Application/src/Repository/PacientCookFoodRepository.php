<?php

namespace App\Repository;

use App\Entity\PacientCookFood;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientCookFood>
 *
 * @method PacientCookFood|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientCookFood|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientCookFood[]    findAll()
 * @method PacientCookFood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientCookFoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientCookFood::class);
    }

    public function add(PacientCookFood $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientCookFood $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCookDataByPacientAndInterval($pacientId, $startDate, $endDate)
    {
        return $this->createQueryBuilder('cf')
            ->select("cf.id, cf.foodOption, cf.observations, DATE_FORMAT(cf.date, '%e %b') AS date")
            ->where('cf.pacient = :pacientId')
            ->andWhere('cf.date >= :startDate')
            ->andWhere('cf.date <= :endDate')
            ->setParameter('pacientId', $pacientId)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('cf.date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @param $date
     * @param null $nursingHome
     * @return float|int|mixed|string
     */
    public function findCookFoodItemsByDate(User $user, $date, $nursingHome = null)
    {
        $queryBuilder = $this->createQueryBuilder('cf')
            ->join('cf.pacient', 'p')
            ->join('p.nursingHome', 'nh')
            ->where('cf.date = :date')
            ->setParameter('date', $date);

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.user = :user')
                ->setParameter('user', $user);
        } else {
            $queryBuilder
                ->andWhere('nh.id = :nursingHome')
                ->setParameter('nursingHome', $user->getNursingHome());
        }

        if (!empty($nursingHome)) {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $nursingHome);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
