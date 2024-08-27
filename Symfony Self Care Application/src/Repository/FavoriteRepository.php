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
     * @param User $user
     * @return float|bool|int|string|null
     */
    public function countFavorites(User $user): float|bool|int|string|null
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @param string $type
     * @param string $sortName
     * @param string $sortOrder
     * @param int $limit
     * @param int $offset
     * @param bool $isCount
     * @return mixed
     */
    public function getFavoritesByFilters(User $user, string $type = '', string $sortName = 'createdAt', string $sortOrder = 'DESC', int $limit = 9, int $offset = 0, bool $isCount = false): mixed
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user);

        // Filter by @categorySlug
        if (!empty($type)) {
            $queryBuilder
                ->andWhere('f.type = :type')
                ->setParameter('type', $type);
        }


        if ($isCount) {
            return $queryBuilder
                ->select("COUNT(DISTINCT f.id)")
                ->getQuery()
                ->getSingleScalarResult();
        }

        $queryBuilder
            ->leftJoin('App\Entity\Company', 'c', 'WITH', 'c.id = f.entityId AND f.type = :careType')
            ->leftJoin('App\Entity\Company', 'p', 'WITH', 'p.id = f.entityId AND f.type = :providerType')
            ->leftJoin('App\Entity\Job', 'j', 'WITH', 'j.id = f.entityId AND f.type = :jobType')
            ->leftJoin('App\Entity\JobTranslation', 'jt', 'WITH', "jt.job = j.id")
            ->leftJoin('App\Entity\TrainingCourse', 't', 'WITH', 't.id = f.entityId AND f.type = :courseType')
            ->leftJoin('App\Entity\TrainingCourseTranslation', 'ct', 'WITH', "ct.trainingCourse = t.id")
            ->setParameter('careType', Favorite::CARE_FAVORITE)
            ->setParameter('providerType', Favorite::PROVIDER_FAVORITE)
            ->setParameter('jobType', Favorite::JOB_FAVORITE)
            ->setParameter('courseType', Favorite::COURSE_FAVORITE);

        return $queryBuilder
            ->select("
            f.uuid, 
            f.type, 
            COALESCE(c.name, p.name, jt.title, ct.title)  as name,
            COALESCE(c.slug, p.slug, j.slug, t.slug)  as slug,
            COALESCE(c.fileName, p.fileName, j.fileName, t.fileName, '$defaultImage')  as image,
            COALESCE(c.address, p.address, j.address, t.address)  as address,
            DATE_FORMAT(f.createdAt, '%d-%m-%Y') as createdAt
        ")
            ->groupBy('f.id')
            ->orderBy("f.$sortName", $sortOrder)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
