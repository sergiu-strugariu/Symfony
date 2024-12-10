<?php

namespace App\Repository;

use App\Entity\DocumentData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentData>
 *
 * @method DocumentData|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentData|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentData[]    findAll()
 * @method DocumentData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentData::class);
    }

    public function add(DocumentData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocumentData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
