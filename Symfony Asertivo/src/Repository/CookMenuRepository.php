<?php

namespace App\Repository;

use App\Entity\CookMenu;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CookMenu>
 *
 * @method CookMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method CookMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method CookMenu[]    findAll()
 * @method CookMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CookMenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CookMenu::class);
    }

    public function add(CookMenu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CookMenu $entity, bool $flush = false): void
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
    public function findDataForDatatable(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('cm')
            ->select("cm.id, cm.name, cm.uid, DATE_FORMAT(cm.startDate, '%d-%m-%Y') as startDate, DATE_FORMAT(cm.endDate, '%d-%m-%Y') as endDate, DATE_FORMAT(cm.createdAt, '%d-%m-%Y') as createdAt")
            ->join('cm.nursingHome', 'nh');

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
            ->orderBy("cm.createdAt", "DESC")
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @param $startDate
     * @param $endDate
     * @param $nursingHome
     * @return float|int|mixed|string
     */
    public function findCookMenusByInterval(User $user, $startDate, $endDate, $nursingHome = null)
    {
        $queryBuilder = $this->createQueryBuilder('cm')
            ->join('cm.nursingHome', 'nh')
            ->where('cm.startDate >= :startDate')
            ->andWhere('cm.endDate <= :endDate');

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
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('cm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
