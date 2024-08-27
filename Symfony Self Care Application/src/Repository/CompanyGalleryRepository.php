<?php

namespace App\Repository;

use App\Entity\CompanyGallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyGallery>
 *
 * @method CompanyGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyGallery[]    findAll()
 * @method CompanyGallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyGalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyGallery::class);
    }

    /**
     * @param $company
     * @return mixed[]
     */
    public function getGalleryByCompany($company): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.company = :val')
            ->setParameter('val', $company)
            ->getQuery()
            ->getArrayResult();
    }
}
