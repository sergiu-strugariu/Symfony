<?php

namespace App\Repository;

use App\Entity\PacientCommentFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientCommentFile>
 *
 * @method PacientCommentFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientCommentFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientCommentFile[]    findAll()
 * @method PacientCommentFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientCommentFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacientCommentFile::class);
    }

    public function add(PacientCommentFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientCommentFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
