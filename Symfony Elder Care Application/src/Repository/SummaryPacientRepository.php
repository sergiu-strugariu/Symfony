<?php

namespace App\Repository;

use App\Entity\SummaryPacient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SummaryPacient>
 *
 * @method SummaryPacient|null find($id, $lockMode = null, $lockVersion = null)
 * @method SummaryPacient|null findOneBy(array $criteria, array $orderBy = null)
 * @method SummaryPacient[]    findAll()
 * @method SummaryPacient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummaryPacientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SummaryPacient::class);
    }

    public function add(SummaryPacient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SummaryPacient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
