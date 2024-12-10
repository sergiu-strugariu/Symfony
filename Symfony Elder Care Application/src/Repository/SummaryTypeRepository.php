<?php

namespace App\Repository;

use App\Entity\SummaryType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SummaryType>
 *
 * @method SummaryType|null find($id, $lockMode = null, $lockVersion = null)
 * @method SummaryType|null findOneBy(array $criteria, array $orderBy = null)
 * @method SummaryType[]    findAll()
 * @method SummaryType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummaryTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SummaryType::class);
    }

    public function add(SummaryType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SummaryType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
