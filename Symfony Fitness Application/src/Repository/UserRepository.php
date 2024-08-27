<?php

namespace App\Repository;

use App\Entity\Education;
use App\Entity\EducationRegistration;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getUsersChartStats()
    {
        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as count, DATE_FORMAT(u.createdAt, \'%d\') as day')
            ->where('u.createdAt >= :startOfMonth')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('startOfMonth', (new \DateTime())->modify('first day of this month'))
            ->setParameter('role', '%"ROLE_CLIENT"%')
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery();

        $result = $query->getResult();
        $daysInMonth = (int) (new \DateTime())->format('t');
        $counts['counts'] = array_fill(1, $daysInMonth, 0);

        foreach ($result as $row) {
            $counts['counts'][(int) $row['day']] = (int) $row['count'];
            $counts['daysWithUsers'][(int) $row['day']] = (int) $row['count'];
        }


        $counts['counts'] = array_values($counts['counts']);
        $counts['daysWithUsers'] = array_values($counts['daysWithUsers']);
        $counts['currentMonth'] = now()->format('F');

        return $counts;
    }

    public function findTotalCount(string $role): float|bool|int|string|null
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param $role
     * @param \DateTimeInterface|null $startDate
     * @param \DateTimeInterface|null $endDate
     * @return array
     */
    public function findByFilters(string $column, string $dir, $keyword, $role = null, \DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select("
                u.id,
                u.uuid,
                u.firstName,
                u.email,
                u.enabled,
                DATE_FORMAT(u.createdAt, '%d-%m-%Y') as createdAt,
                DATE_FORMAT(u.lastLoginAt, '%d-%m-%Y %H:%i') as lastLoginAt"
            )
            ->where('u.deletedAt IS NULL');

        // Field search
        $fields = [
            'u.id',
            'u.firstName',
            'u.email'
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
        
        if (null !== $role) {
            $queryBuilder->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%"' . $role . '"%');
        }

        if ($startDate && $endDate) {
            $queryBuilder
                ->andWhere('u.createdAt BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Dynamic order by
        return $queryBuilder
            ->orderBy('u.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }
}
