<?php

namespace App\Repository;

use App\Entity\County;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<County>
 *
 * @method County|null find($id, $lockMode = null, $lockVersion = null)
 * @method County|null findOneBy(array $criteria, array $orderBy = null)
 * @method County[]    findAll()
 * @method County[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, County::class);
    }

    public function add(County $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(County $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function findCounties(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;
    }

}
