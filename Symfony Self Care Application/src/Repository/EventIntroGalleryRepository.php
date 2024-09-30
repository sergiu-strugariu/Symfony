<?php

namespace App\Repository;

use App\Entity\EventIntroGallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventIntroGallery>
 *
 * @method EventIntroGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventIntroGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventIntroGallery[]    findAll()
 * @method EventIntroGallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventIntroGalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventIntroGallery::class);
    }

    //    /**
    //     * @return EventIntroGallery[] Returns an array of EventIntroGallery objects
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

    //    public function findOneBySomeField($value): ?EventIntroGallery
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
