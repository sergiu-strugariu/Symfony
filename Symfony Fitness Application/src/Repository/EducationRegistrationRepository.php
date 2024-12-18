<?php

namespace App\Repository;

use App\Entity\Education;
use App\Entity\EducationRegistration;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationRegistration>
 *
 * @method EducationRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method EducationRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method EducationRegistration[]    findAll()
 * @method EducationRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationRegistration::class);
    }


    public function findTotalCount($uuid = null)
    {
        $queryBuilder = $this->createQueryBuilder('er')
            ->select('COUNT(er.id)')
            ->andWhere('er.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return
            $queryBuilder->getQuery()
                ->getSingleScalarResult();
    }
    
    public function findRegistrationsCountByUser($user)
    {
        return $this->createQueryBuilder('er')
            ->select('COUNT(er.id)')
            ->join('er.education', 'e')
            ->where('er.user = :user')
            ->andWhere('er.paymentStatus = :status')
            ->andWhere('e.deletedAt is NULL')
            ->setParameter('user', $user)
            ->setParameter('status', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByFilters(string $column, string $dir, $keyword, $uuid): array
    {
        $queryBuilder = $this->createQueryBuilder('er')
            ->join('er.education', 'e')
            ->select("
                er.id,
                er.uuid,
                er.firstName,
                er.lastName,
                er.email,
                er.phone,
                er.cnp,
                er.paymentAmount,
                er.paymentStatus,
                er.paymentMethod,
                er.payuPaymentReference"
            )
            ->where('e.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        // Field search
        $fields = [
            'er.id',
            'er.firstName',
            'er.lastName',
            'er.email',
            'er.phone',
            'er.cnp',
            'er.paymentAmount',
            'er.paymentStatus',
            'er.paymentMethod',
            'er.payuPaymentReference'
        ];

        // Check @keyword
        if (isset($keyword)) {
            $orExpr = $queryBuilder->expr()->orX();

            // Search in all fields
            foreach ($fields as $field) {
                $orExpr->add($queryBuilder->expr()->like($field, ':keyword'));
            }

            $queryBuilder
                ->andWhere($orExpr)
                ->setParameter('keyword', '%' . $keyword . '%');
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('er.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    public function getDataByMonthAndYear(int $year, string $type = Education::TYPE_COURSE): mixed
    {
        $queryBuilder = $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count')
            ->where('YEAR(entity.createdAt) = :year')
            ->andWhere('entity.type = :type')
            ->andWhere('entity.deletedAt IS NULL');

        return $queryBuilder
            ->setParameter('type', $type)
            ->setParameter('year', $year)
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()
            ->getResult();
    }

    public function getDataByEducation($educationId, string $type = Education::TYPE_COURSE): mixed
    {
        $queryBuilder = $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count, SUM(entity.paymentAmount * (1 + entity.paymentVat / 100)) as price')
            ->join('entity.education', 'education')
            ->where('education.type = :type')
            ->andWhere('education.deletedAt IS NULL');

        if ($educationId !== 'all') {
            $queryBuilder->andWhere('entity.education = :educationId')
                ->setParameter('educationId', $educationId);
        }

        return $queryBuilder
            ->setParameter('type', $type)
            ->groupBy('year, month')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC')
            ->getQuery()
            ->getResult();


    }

    public function getUserDataByMonthAndYear(int $year, string $type = Education::TYPE_COURSE, string $status = EducationRegistration::PAYMENT_STATUS_SUCCESS)
    {
        return $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count')
            ->join('entity.education', 'education')
            ->where('education.type = :type')
            ->andWhere('entity.paymentStatus = :status')
            ->andWhere('YEAR(entity.createdAt) = :year')
            ->andWhere('education.deletedAt IS NULL')
            ->setParameter('type', $type)
            ->setParameter('status', $status)
            ->setParameter('year', $year)
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()
            ->getResult();
    }

    public function getCalendarEducationRegistrations($user, $status, $order, $showFutureEducations = true, $showPreviousEducations = true)
    {
        $qb = $this->createQueryBuilder('entity')
                ->join('entity.education', 'education')
                ->where('entity.user = :user')
                ->andWhere('entity.paymentStatus = :status')
                ->setParameter('status', $status)
                ->setParameter('user', $user);

        if (!$showFutureEducations) {
            $qb->andWhere('education.startDate <= :now')
                    ->setParameter('now', new \DateTime());
        }
        
        if (!$showPreviousEducations) {
            $qb->andWhere('education.endDate >= :now')
                    ->setParameter('now', new \DateTime());
        }

        return $qb->orderBy('education.startDate', $order)
                        ->getQuery()
                        ->getResult();
    }

    public function getEducationRegistrationCount($user, $startDate = null, $endDate = null)
    {
        $qb = $this->createQueryBuilder('entity')
            ->select('COUNT(DISTINCT entity.education)')
            ->where('entity.user = :user')
            ->setParameter('user', $user)
            ->andWhere('entity.paymentStatus = :status')
            ->setParameter('status', EducationRegistration::PAYMENT_STATUS_SUCCESS);

        if ($startDate !== null && $endDate !== null) {
            $qb
                ->andWhere('entity.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findMaxContractNumber()
    {
        return $this->createQueryBuilder('entity')
            ->select('MAX(entity.contractNumber)')
            ->where('entity.paymentStatus = :status')
            ->setParameter('status', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUserYears(): array
    {
        return $this->createQueryBuilder('entity')
            ->select('DISTINCT YEAR(entity.createdAt) as year')
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function getCertifications($user)
    {
        return $this->createQueryBuilder('er')
            ->where('er.user = :user')
            ->andWhere('er.certificateFileName IS NOT NULL')
            ->andWhere('er.paymentStatus = :status')
            ->setParameter('user', $user)
            ->setParameter('status', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->getQuery()
            ->getResult();
    }
    
    public function findPendingPaymentsForReminder() {
        return $this->createQueryBuilder('er')
            ->where('er.paymentMethod = :type')
            ->andWhere('er.paymentStatus = :status')
            ->andWhere('er.reminderSent = 0')
            ->andWhere('er.createdAt <= :date')
            ->setParameter('type', EducationRegistration::PAYMENT_TYPE_WIRE)
            ->setParameter('status', EducationRegistration::PAYMENT_STATUS_PENDING)
            ->setParameter('date', new \DateTime('-3 days'))
            ->getQuery()
            ->getResult();
    }

    public function getContracts($user): array
    {
        return $this->createQueryBuilder('er')
            ->select("DATE_FORMAT(er.createdAt, '%M %d, %Y') as createdAt,
                er.contractNumber,
                et.title AS educationTitle,
                e.slug,
                er.uuid")
            ->join('er.education', 'e')
            ->join('e.educationTranslations', 'et')
            ->join('et.language', 'l')
            ->where('er.paymentStatus = :paymentStatus')
            ->andWhere('e.deletedAt IS NULL')
            ->andWhere('er.user = :user')
            ->andWhere('l.locale = :locale')
            ->setParameter('user', $user)
            ->setParameter('paymentStatus', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->setParameter('locale', 'ro')
            ->orderBy('er.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function getInvoices($user): array
    {
        return $this->createQueryBuilder('er')
            ->select("DATE_FORMAT(er.createdAt, '%M %d, %Y') as createdAt,
                er.invoiceSeriesName,
                er.invoiceNumber,
                et.title AS educationTitle,
                er.uuid,
                e.slug")
            ->join('er.education', 'e')
            ->join('e.educationTranslations', 'et')
            ->join('et.language', 'l')
            ->where('er.paymentStatus = :paymentStatus')
            ->andWhere('e.deletedAt IS NULL')
            ->andWhere('er.user = :user')
            ->andWhere('l.locale = :locale')
            ->setParameter('user', $user)
            ->setParameter('paymentStatus', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->setParameter('locale', 'ro')
            ->orderBy('er.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function getCourses($user): array
    {
        return $queryBuilder = $this->createQueryBuilder('er')
            ->select("
                DATE_FORMAT(e.startDate, '%M %d, %Y') as createdAt,
                et.title as educationTitle,
                e.uuid")
            ->join('er.education', 'e')
            ->join('e.educationTranslations', 'et')
            ->join('et.language', 'l')
            ->where('er.paymentStatus = :paymentStatus')
            ->andWhere('e.deletedAt IS NULL')
            ->andWhere('er.user = :user')
            ->andWhere('l.locale = :locale')
            ->setParameter('user', $user)
            ->setParameter('paymentStatus', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->setParameter('locale', 'ro')
            ->orderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function getUserCertifications($user): array
    {
        return $this->createQueryBuilder('er')
            ->select("
                DATE_FORMAT(er.createdAt, '%M %d, %Y') as createdAt,
                et.title as educationTitle,
                er.certificateFileName as certificationName
            ")
            ->join('er.education', 'e')
            ->join('e.educationTranslations', 'et')
            ->join('et.language', 'l')   
            ->where('er.user = :user')
            ->andWhere('e.deletedAt IS NULL')
            ->andWhere('er.certificateFileName IS NOT NULL')
            ->andWhere('er.paymentStatus = :status')
            ->andWhere('l.locale = :locale')
            ->setParameter('user', $user)
            ->setParameter('status', EducationRegistration::PAYMENT_STATUS_SUCCESS)
            ->setParameter('locale', 'ro')
            ->orderBy('er.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
