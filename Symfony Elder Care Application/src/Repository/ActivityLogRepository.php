<?php

namespace App\Repository;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 *
 * @method ActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityLog[]    findAll()
 * @method ActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityLogRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    public function add(ActivityLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActivityLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveMonths(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->select("DATE_FORMAT(al.createdAt, '%Y-%m') as month")
            ->join('al.nursingHome', 'nh');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->where('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->where('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        return $queryBuilder
            ->orderBy('al.createdAt', 'DESC')
            ->groupBy('month')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @param $date
     * @param null $nursingHome
     * @return float|int|array|string
     */
    public function findMonthlyActivityLog(User $user, $date, $nursingHome = null)
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->join('al.nursingHome', 'nh')
            ->select("COUNT(DISTINCT(al.user)) AS usersCount, DATE_FORMAT(al.createdAt, '%Y-%m-%d') as date")
            ->where("DATE_FORMAT(al.createdAt, '%Y-%m') = :date")
            ->setParameter('date', $date);

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if (!empty($nursingHome)) {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id',$nursingHome);
        }

        return $queryBuilder
            ->orderBy('al.createdAt', 'ASC')
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult();
    }

    public function findDailyActivityLog($date, $nursingHome = null)
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->select("u.id, u.uid, u.roles, u.photo, u.firstName, u.lastName, COUNT(al.page) AS pagesCount, DATE_FORMAT(MIN(al.createdAt), '%H:%i') AS firstView, DATE_FORMAT(MAX(al.createdAt), '%H:%i') AS lastView")
            ->join('al.user', 'u')
            ->where("DATE_FORMAT(al.createdAt, '%Y-%m-%d') = :date")
            ->setParameter('date', $date)
            ->orderBy('u.lastName', 'ASC')
            ->groupBy('u.id');

        if (null !== $nursingHome) {
            $queryBuilder->andWhere('u.nursingHome = :nursingHome')
                ->setParameter('nursingHome', $nursingHome);
        }

        $data = $queryBuilder->getQuery()
            ->getArrayResult();

        $package = new UrlPackage(User::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());
        return array_map(function ($item) use ($package) {
            // reformat fields
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);
            $item['photo'] = (empty($item['photo'])) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $item['photo']));
            $item['role'] = is_array($item['roles']) && isset($item['roles'][0]) ? User::getRoleAsString($item['roles'][0]) : '-';

            return $item;
        }, $data);
    }

}
