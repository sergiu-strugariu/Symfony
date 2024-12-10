<?php

namespace App\Repository;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Prospect;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Prospect>
 *
 * @method Prospect|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prospect|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prospect[]    findAll()
 * @method Prospect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProspectRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prospect::class);
    }

    public function add(Prospect $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Prospect $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user
     * @param string $type
     * @param string|null $date
     * @param string|null $dateFormat
     * @param string|null $status
     * @return array
     */
    public function findProspectsForDatatable(User $user, string $type, ?string $date = '', ?string $dateFormat = '%m-%Y', ?string $status = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.createdAt, p.relationName, p.offerPrice, p.leadSource, p.beneficiaryName, p.phoneNumber, p.email, p.scheduledAt, p.offerSentAt, u.firstName, u.lastName, u.photo, p.status, p.uid')
            ->join('p.nursingHome', 'nh')
            ->leftJoin('p.addedBy', 'u');

        $column = 'p.createdAt';
        switch ($type) {
            case 'scheduled':
                $column = 'p.scheduledAt';
                $queryBuilder->where("{$column} IS NOT NULL");
                break;
            case 'offers':
                $column = 'p.offerSentAt';
                $queryBuilder->where("{$column} IS NOT NULL");
                break;
            default:
                break;
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if (!empty($date)) {
            $queryBuilder
                ->andWhere("DATE_FORMAT({$column}, '{$dateFormat}') = :date")
                ->setParameter('date', $date);
        }

        if (!empty($status)) {
            $queryBuilder
                ->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        $data = $queryBuilder
            ->getQuery()
            ->getArrayResult();
        $package = new UrlPackage(User::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());

        return array_map(function ($item) use ($package) {
            // reformat fields
            $item['createdAt'] = ($item['createdAt'] instanceof \DateTime) ? $item['createdAt']->format('d-m-Y') : '-';
            $item['scheduledAt'] = ($item['scheduledAt'] instanceof \DateTime) ? $item['scheduledAt']->format('d-m-Y H:i') : '-';
            $item['offerSentAt'] = ($item['offerSentAt'] instanceof \DateTime) ? $item['offerSentAt']->format('d-m-Y H:i') : '-';
            $item['addedByName'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);
            $item['addedByPhoto'] = (empty($item['photo'])) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $item['photo']));


            return $item;
        }, $data);
    }

    public function findProspectsCountByStatus($status, $date = null, $dateFormat = '%m-%Y')
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->setParameter('status', $status);

        if (!empty($date)) {
            $queryBuilder->andWhere("DATE_FORMAT(p.createdAt, '{$dateFormat}') = :date")
                ->setParameter('date', $date);
        }

        return $queryBuilder->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @return float|int|mixed|string
     */
    public function findProspectsSources(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.leadSource AS source, COUNT(p.leadSource) AS count')
            ->join('p.nursingHome', 'nh')
            ->where('p.leadSource IS NOT NULL');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        return $queryBuilder
            ->groupBy('p.leadSource')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param $year
     * @return float|int|mixed|string
     */
    public function findProspectsCountByStatusAndMonth(User $user, $year)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.status, COUNT(p) as count, MONTH(p.createdAt) AS month')
            ->join('p.nursingHome', 'nh')
            ->where('YEAR(p.createdAt) = :year')
            ->setParameter('year', $year);

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        return $queryBuilder
            ->groupBy('month, p.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param $year
     * @param bool $rejected
     * @param bool $accepted
     * @return float|int|mixed|string
     */
    public function findTotalOffersByMonth(User $user, $year, bool $rejected = false, bool $accepted = false)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('MONTH(p.offerSentAt) AS month, SUM(p.offerPrice) AS total, COUNT(p) AS count')
            ->join('p.nursingHome', 'nh')
            ->where('p.offerSentAt IS NOT NULL')
            ->andWhere('YEAR(p.createdAt) = :year')
            ->setParameter('year', $year);

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if ($rejected) {
            $queryBuilder
                ->andWhere('p.status = :status')
                ->setParameter('status', Prospect::STATUS_REJECTED);
        }

        if ($accepted) {
            $queryBuilder
                ->andWhere('p.status = :status')
                ->setParameter('status', Prospect::STATUS_ACCEPTED);
        }

        return $queryBuilder
            ->groupBy('month')
            ->getQuery()
            ->getResult();
    }

    public function findViewingsByMonth($year, $canceled = false, $scheduled = false)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('MONTH(p.scheduledAt) AS month, COUNT(p) AS total')
            ->where('p.scheduledAt IS NOT NULL')
            ->andWhere('YEAR(p.createdAt) = :year')
            ->setParameter('year', $year);

        if ($canceled) {
            $queryBuilder->andWhere('p.canceled = 1');
        }

        if ($scheduled) {
            $queryBuilder->andWhere('p.canceled = 0');
        }

        return $queryBuilder->groupBy('month')
            ->getQuery()
            ->getResult();
    }

}
