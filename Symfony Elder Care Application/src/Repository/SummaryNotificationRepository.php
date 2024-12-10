<?php

namespace App\Repository;

use App\Entity\SummaryNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SummaryNotification>
 *
 * @method SummaryNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method SummaryNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method SummaryNotification[]    findAll()
 * @method SummaryNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummaryNotificationRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, SummaryNotification::class);
    }

    public function add(SummaryNotification $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SummaryNotification $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSummaryNotificationsByPacient($pacient) {
        $data = $this->createQueryBuilder('sn')
                ->select("DATE_FORMAT(sn.notificationDate, '%d/%m/%Y') as notificationDate, sn.observations, u.firstName, u.lastName")
                ->leftJoin('sn.createdBy', 'u')
                ->where('sn.pacient = :pacient')
                ->setParameter('pacient', $pacient)
                ->orderBy('sn.id', 'DESC')
                ->getQuery()
                ->getArrayResult()
        ;

        return array_map(function ($item) {
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    public function findSummaryNotificationsByUser($user, $date = null) {
        $queryBuilder = $this->createQueryBuilder('sn')
                ->where('sn.createdBy = :user')
                ->setParameter('user', $user);

        if ($date !== null) {
            $queryBuilder->andWhere('DATE(sn.notificationDate) = :date')
                    ->setParameter('date', $date);
        }

        return $queryBuilder->getQuery()
                        ->getResult();
    }

}
