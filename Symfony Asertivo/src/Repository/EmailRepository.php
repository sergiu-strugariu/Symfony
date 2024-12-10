<?php

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Email>
 *
 * @method Email|null find($id, $lockMode = null, $lockVersion = null)
 * @method Email|null findOneBy(array $criteria, array $orderBy = null)
 * @method Email[]    findAll()
 * @method Email[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Email::class);
    }

    public function add(Email $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Email $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEmailsByRecipient($recipientEmail): array {
        return $this->createQueryBuilder('e')
                        ->select("DATE_FORMAT(e.createdAt, '%d/%m/%Y') as createdAt, e.senderEmail, e.recipientEmail, e.subject, e.opened, e.clicks")
                        ->where('e.recipientEmail = :recipientEmail')
                        ->setParameter('recipientEmail', $recipientEmail)
                        ->orderBy('e.createdAt', 'DESC')
                        ->getQuery()
                        ->getArrayResult()
        ;
    }

}
