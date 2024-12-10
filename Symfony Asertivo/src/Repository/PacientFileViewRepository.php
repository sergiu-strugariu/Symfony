<?php

namespace App\Repository;

use App\Entity\PacientFileView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientFileView>
 *
 * @method PacientFileView|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientFileView|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientFileView[]    findAll()
 * @method PacientFileView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientFileViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientFileView::class);
    }

    public function add(PacientFileView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientFileView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
