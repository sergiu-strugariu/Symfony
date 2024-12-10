<?php

namespace App\Repository;

use App\Entity\PacientRoom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientRoom>
 *
 * @method PacientRoom|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientRoom|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientRoom[]    findAll()
 * @method PacientRoom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientRoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientRoom::class);
    }

    public function add(PacientRoom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientRoom $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
