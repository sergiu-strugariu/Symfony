<?php

namespace App\Repository;

use App\Entity\PacientMedication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientMedication>
 *
 * @method PacientMedication|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientMedication|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientMedication[]    findAll()
 * @method PacientMedication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientMedicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientMedication::class);
    }

    public function add(PacientMedication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientMedication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
