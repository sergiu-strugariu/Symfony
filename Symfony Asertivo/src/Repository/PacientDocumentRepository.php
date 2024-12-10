<?php

namespace App\Repository;

use App\Entity\PacientDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientDocument>
 *
 * @method PacientDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientDocument[]    findAll()
 * @method PacientDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientDocument::class);
    }

    public function add(PacientDocument $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientDocument $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
