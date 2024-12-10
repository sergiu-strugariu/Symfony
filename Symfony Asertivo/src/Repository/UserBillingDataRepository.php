<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBillingData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBillingData>
 *
 * @method UserBillingData|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBillingData|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBillingData[]    findAll()
 * @method UserBillingData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBillingDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBillingData::class);
    }

    public function add(UserBillingData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserBillingData $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array|null
     */
    public function getBillingAddress(User $user, string $uuid = '', bool $isOneResult = false)
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->leftJoin('a.county', 'cty')
            ->leftJoin('a.city', 'ciy')
            ->select("
            a.id,
            a.uuid,
            a.companyName,
            a.companyRegisterNumber,
            a.cui,
            a.iban,
            a.email,
            a.phone,
            a.address,
            a.isFavorite,
            cty.id as countyId,
            cty.code as countyCode,
            cty.name as countyName,
            ciy.id as cityId,
            ciy.name as cityName")
            ->where('a.user = :user')
            ->setParameter('user', $user);

        if (!empty($uuid)) {
            $queryBuilder
                ->andWhere('a.uuid = :uuid')
                ->setParameter('uuid', $uuid);
        }

        return $queryBuilder
            ->addOrderBy('a.isFavorite', 'DESC')
            ->getQuery()
            ->{$isOneResult ? 'getOneOrNullResult' : 'getResult'}();
    }

}
