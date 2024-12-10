<?php

namespace App\Repository;

use App\Entity\PacientFileType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientFileType>
 *
 * @method PacientFileType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientFileType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientFileType[]    findAll()
 * @method PacientFileType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientFileTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientFileType::class);
    }

    public function add(PacientFileType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientFileType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
