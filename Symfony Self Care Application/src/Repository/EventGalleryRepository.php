<?php

namespace App\Repository;

use App\Entity\EventGallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventGallery>
 *
 * @method EventGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventGallery[]    findAll()
 * @method EventGallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventGalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventGallery::class);
    }

    //    /**
    //     * @return EventGallery[] Returns an array of EventGallery objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EventGallery
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
