<?php

namespace App\Repository;

use App\Entity\Pacient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @extends ServiceEntityRepository<Pacient>
 *
 * @method Pacient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pacient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pacient[]    findAll()
 * @method Pacient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pacient::class);
    }

    public function add(Pacient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pacient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPacients($nursingHome)
    {
        $data = $this->createQueryBuilder('p')
            ->select("p.id, p.photo, p.firstName, p.lastName, p.status, p.email, p.phoneNumber, p.cnp, DATE_FORMAT(p.createdAt, '%d/%m/%Y') as createdAt, nh.name as nursingHome, p.dateOfBirth, p.uid")
            ->leftJoin('p.nursingHome', 'nh')
            ->where('nh.id = :id')
            ->setParameter('id', $nursingHome->getId())
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat datetime
            $item['dateOfBirth'] = ($item['dateOfBirth'] instanceof \DateTime) ? $item['dateOfBirth']->format('d-m-Y') : '-';
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    /**
     * @param $user
     * @param bool $showAdmittedOnly
     * @return array
     */
    public function findPacientsForDatatable($user, bool $showAdmittedOnly): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->join('p.nursingHome', 'nh')
            ->leftJoin('p.nursingHomeRoom', 'nhr');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->where('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->where('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        if ($showAdmittedOnly) {
            $queryBuilder
                ->andWhere('p.status = :status')
                ->setParameter('status', Pacient::STATUS_ADMITTED);
        }

        $data = $queryBuilder
            ->select('p.photo, p.firstName, p.lastName, nhr.roomNumber, p.status, p.cnp, p.id, p.uid')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // Reformat datetime
            $item['admissionDate'] = '-';
            $item['dischargeDate'] = '-';
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    /**
     * @param User $user
     * @param null $status
     * @param bool $floor
     * @param null $roomId
     * @param null $nursingHome
     * @return array
     */
    public function findPacientsByNursingHome(User $user, $status = null, bool $floor = false, $roomId = null, $nursingHome = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.firstName, p.lastName, nhr.roomNumber, nhr.floor, p.id, p.uid, p.cnp, p.status')
            ->join('p.nursingHome', 'nh')
            ->leftJoin('p.nursingHomeRoom', 'nhr')
            ->where('p.id IS NOT NULL');

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
                ->setParameter('id', $nursingHome);
        }

        if (!empty($status)) {
            $queryBuilder
                ->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($floor)) {
            $queryBuilder
                ->andWhere('nhr.floor = :floor')
                ->setParameter('floor', $floor);
        }

        if (!empty($roomId)) {
            $queryBuilder
                ->andWhere('nhr.id = :roomId')
                ->setParameter('roomId', $roomId);
        }

        $data = $queryBuilder
            ->orderBy('p.lastName', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat fields
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    /**
     * @param $nursingHomeRoom
     * @return array
     */
    public function findPacientsByRoom($nursingHomeRoom): array
    {
        $data = $this->createQueryBuilder('p')
            ->select('p.id, p.uid, p.firstName, p.lastName')
            ->where('p.nursingHomeRoom = :nursingHomeRoom')
            ->andWhere('p.status != :status')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('nursingHomeRoom', $nursingHomeRoom)
            ->setParameter('status', Pacient::STATUS_DISCHARGED)
            ->orderBy('p.lastName', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat fields
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    public function searchPacients($nursingHome, $query, $status = null)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.photo, p.firstName, p.lastName, p.email, p.uid')
            ->where("p.firstName LIKE :query OR p.lastName LIKE :query OR p.email LIKE :query")
            ->andWhere("p.nursingHome = :nursingHome")
            ->setParameter('query', '%' . addcslashes($query, '%_') . '%')
            ->setParameter('nursingHome', $nursingHome);

        if (null !== $status) {
            $queryBuilder->andWhere("p.status = :status")
                ->setParameter('status', $status);
        }

        $data = $queryBuilder->getQuery()
            ->getArrayResult();

        $package = new UrlPackage(User::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());
        return array_map(function ($item) use ($package) {
            // reformat datetime
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);
            $item['photo'] = (empty($item['photo'])) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $item['photo']));

            return $item;
        }, $data);
    }

    /**
     * @param User $user
     * @param null $status
     * @param bool $floor
     * @param null $roomId
     * @param null $nursingHome
     * @return array
     */
    public function findPacientsDiagnosesTreatmentsForReport(User $user, $status = null, bool $floor = false, $roomId = null, $nursingHome = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.id, p.firstName, p.lastName, ua.firstName AS auditorFirstName, ua.lastName AS auditorLastName, ua.photo as auditorPhoto, p.treatmentPlanAuditObservations, p.treatmentPlanAuditDate, p.treatmentPlanValid, p.status, nhr.roomNumber, nhr.floor, p.uid, p.cnp, COUNT(DISTINCT(pd.id)) AS diagnosesCount, COUNT(DISTINCT(pmd.drug)) AS drugsCount')
            ->join('p.nursingHome', 'nh')
            ->leftJoin('p.nursingHomeRoom', 'nhr')
            ->leftJoin('p.pacientDiagnoses', 'pd')
            ->leftJoin('p.pacientMedicationDetails', 'pmd')
            ->leftJoin('p.treatmentPlanAuditedBy', 'ua')
            ->where('p.id IS NOT NULL');

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
                ->setParameter('id', $nursingHome);
        }

        if (!empty($status)) {
            $queryBuilder
                ->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($floor)) {
            $queryBuilder
                ->andWhere('nhr.floor = :floor')
                ->setParameter('floor', $floor);
        }

        if (!empty($roomId)) {
            $queryBuilder
                ->andWhere('nhr.id = :roomId')
                ->setParameter('roomId', $roomId);
        }

        $data = $queryBuilder
            ->orderBy('p.lastName', 'ASC')
            ->groupBy('p.id')
            ->getQuery()
            ->getArrayResult();

        $package = new UrlPackage(User::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());

        return array_map(function ($item) use ($package) {
            // reformat fields
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);
            $item['auditorName'] = (empty($item['auditorFirstName']) && empty($item['auditorLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['auditorFirstName'], $item['auditorLastName']);
            $item['auditorPhoto'] = (empty($item['auditorPhoto'])) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $item['auditorPhoto']));

            return $item;
        }, $data);
    }

    public function findPacientsCountByStatus($nursingHome, $status)
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->andWhere('p.nursingHome = :nursingHome')
            ->setParameter('status', $status)
            ->setParameter('nursingHome', $nursingHome)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $nursingHome
     * @param $nursingHomeRoom
     * @return array
     */
    public function getPacientsByNursingHome($nursingHome, $nursingHomeRoom): array
    {
        return $this->createQueryBuilder('p')
            ->select("p.uid, CONCAT(p.firstName, ' ', p.lastName) AS fullName, CASE WHEN p.nursingHomeRoom = :nursingHomeRoom THEN true ELSE false END AS selected")
            ->where('p.deletedAt IS NULL')
            ->andWhere('p.nursingHome = :nursingHome')
            ->andWhere('p.status != :status')
            ->orderBy('p.lastName', 'ASC')
            ->setParameter('nursingHome', $nursingHome)
            ->setParameter('nursingHomeRoom', $nursingHomeRoom)
            ->setParameter('status', Pacient::STATUS_DISCHARGED)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findUserPacients(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select("p.id, p.photo, p.firstName, p.lastName, p.status, p.email, p.phoneNumber, p.cnp, DATE_FORMAT(p.createdAt, '%d/%m/%Y') as createdAt, nh.name as nursingHome, p.dateOfBirth, p.uid")
            ->leftJoin('p.nursingHome', 'nh');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->where('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->where('nh.id = :id')
                ->setParameter('id', $user->getNursingHome());
        }

        $data = $queryBuilder
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            // reformat datetime
            $item['dateOfBirth'] = ($item['dateOfBirth'] instanceof \DateTime) ? $item['dateOfBirth']->format('d-m-Y') : '-';
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);

            return $item;
        }, $data);
    }

    public function getAllPacients(User $user = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.deletedAt IS NULL')
            ->orderBy('p.createdAt', 'DESC');

        if ($user && $user->hasRole('ROLE_ADMIN')) {
            $qb->leftJoin('p.nursingHome', 'nh')
                ->where('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function countAllPacients(User $user = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.deletedAt IS NULL');

        if ($user && $user->hasRole('ROLE_ADMIN')) {
            $qb->leftJoin('p.nursingHome', 'nh')
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        }

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

}
