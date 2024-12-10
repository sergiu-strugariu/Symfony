<?php

namespace App\Repository;

use App\Entity\PacientFileGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientFileGroup>
 *
 * @method PacientFileGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientFileGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientFileGroup[]    findAll()
 * @method PacientFileGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientFileGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientFileGroup::class);
    }

    public function add(PacientFileGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientFileGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
