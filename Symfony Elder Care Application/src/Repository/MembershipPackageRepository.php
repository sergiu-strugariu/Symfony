<?php

namespace App\Repository;

use App\Entity\MembershipPackage;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembershipPackage>
 *
 * @method MembershipPackage|null find($id, $lockMode = null, $lockVersion = null)
 * @method MembershipPackage|null findOneBy(array $criteria, array $orderBy = null)
 * @method MembershipPackage[]    findAll()
 * @method MembershipPackage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembershipPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipPackage::class);
    }

    public function add(MembershipPackage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MembershipPackage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->createQueryBuilder('p')
            ->select("
            p.id,
            p.uuid,
            p.name,
            p.slug,
            p.price,
            p.discount,
            p.status,
            p.fileName,
            DATE_FORMAT(p.createdAt, '%d/%m/%Y') as createdAt")
            ->where("p.deletedAt IS NULL")
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array
     */
    public function getAllPackages(): array
    {
        return $this->createQueryBuilder('mp')
            ->where('mp.deletedAt IS NULL')
            ->andWhere('mp.status = :status')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->getQuery()
            ->getResult();
    }
}
