<?php

namespace App\Repository;

use App\Entity\PacientSummary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientSummary>
 *
 * @method PacientSummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientSummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientSummary[]    findAll()
 * @method PacientSummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientSummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientSummary::class);
    }

    public function add(PacientSummary $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientSummary $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
