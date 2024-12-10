<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * @return mixed
     */
    public function findExpiringPayments(): mixed
    {
        return $this->createQueryBuilder('p')
            ->where('p.membershipExpiresAt < :date')
            ->andWhere('p.status = :status')
            ->andWhere('p.processed = 0')
            ->setParameter('date', new \DateTime('-1 day'))
            ->setParameter('status', Payment::PAYMENT_STATUS_CONFIRMED)
            ->getQuery()
            ->getResult();
    }
}
