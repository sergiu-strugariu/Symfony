<?php

namespace App\Repository;

use App\Entity\PacientRecordView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientRecordView>
 *
 * @method PacientRecordView|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientRecordView|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientRecordView[]    findAll()
 * @method PacientRecordView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientRecordViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientRecordView::class);
    }

    public function add(PacientRecordView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientRecordView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
