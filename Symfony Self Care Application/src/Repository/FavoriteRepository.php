<?php

namespace App\Repository;

use App\Entity\Favorite;
use App\Entity\User;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorite>
 *
 * @method Favorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Favorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Favorite[]    findAll()
 * @method Favorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoriteRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, DefaultHelper $helper)
    {
        parent::__construct($registry, Favorite::class);
        $this->em = $em;
        $this->helper = $helper;
    }

    /**
     * @param User|null $user
     * @param string $type
     * @param string $sortName
     * @param string $sortOrder
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return mixed
     */
    public function getFavoritesByFilters(User $user = null, string $type = '', string $sortName = 'createdAt', string $sortOrder = 'ASC', int $limit = 4, int $offset = 0, bool $isCount = false): mixed
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('f');

        // Filter by user
        if (!empty($user)) {
            $queryBuilder
                ->where('f.user = :user')
                ->setParameter('user', $user);
        }

        // Filter by @type
        if (!empty($type)) {
            $queryBuilder
                ->andWhere('f.type = :type')
                ->setParameter('type', $type);
        }

        // Total items
        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT f.entityId)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        $queryBuilder
            ->leftJoin('App\Entity\Company', 'c', 'WITH', 'c.id = f.entityId AND f.type = :careType')
            ->leftJoin('App\Entity\Company', 'p', 'WITH', 'p.id = f.entityId AND f.type = :providerType')
            ->setParameter('careType', Favorite::CARE_FAVORITE)
            ->setParameter('providerType', Favorite::PROVIDER_FAVORITE);

        return $queryBuilder
            ->select("
            f.uuid, 
            f.type, 
            f.entityId, 
            COALESCE(c.name, p.name)  as name,
            COALESCE(c.slug, p.slug)  as slug,
            COALESCE(c.fileName, p.fileName, '$defaultImage')  as image,
            COALESCE(c.address, p.address)  as address,
            COUNT(f.id) AS totalFavorites,
            DATE_FORMAT(f.createdAt, '%d-%m-%Y') as createdAt
        ")
            ->groupBy('f.entityId')
            ->orderBy("f.$sortName", $sortOrder)
            ->addOrderBy('totalFavorites', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
