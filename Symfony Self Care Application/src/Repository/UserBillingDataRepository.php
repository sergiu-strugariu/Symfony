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

    /**
     * @param User $user
     * @param string $uuid
     * @param bool $isOneResult
     * @return mixed
     */
    public function getBillingAddress(User $user, string $uuid = '', bool $isOneResult = false): mixed
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
