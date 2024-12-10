<?php

namespace App\Repository;

use App\Entity\PacientComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientComment>
 *
 * @method PacientComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientComment[]    findAll()
 * @method PacientComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientCommentRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientComment::class);
    }

    public function add(PacientComment $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientComment $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLatestCommentByPacient($pacientId) {
        return $this->createQueryBuilder('pc')
                        ->join('pc.pacient', 'p')
                        ->where('p.id = :pacientId')
                        ->setParameter('pacientId', $pacientId)
                        ->orderBy('pc.createdAt', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult()
        ;
    }

}
