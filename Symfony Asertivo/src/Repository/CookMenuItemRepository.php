<?php

namespace App\Repository;

use App\Entity\CookMenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CookMenuItem>
 *
 * @method CookMenuItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CookMenuItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CookMenuItem[]    findAll()
 * @method CookMenuItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CookMenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CookMenuItem::class);
    }

    public function add(CookMenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CookMenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
     
}
