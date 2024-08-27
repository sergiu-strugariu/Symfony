<?php

namespace App\Repository;

use App\Entity\User;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

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

    public function countUsers($role): float|bool|int|string|null
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
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     * @return array
     */
    public function findUsersByFilters(string $column, string $dir, $keyword, $role = null, DateTimeInterface $startDate = null, DateTimeInterface $endDate = null): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select("
                u.id,
                u.uuid,
                u.name,
                u.email,
                u.enabled,
                DATE_FORMAT(u.createdAt, '%d-%m-%Y') as createdAt,
                DATE_FORMAT(u.lastLoginAt, '%d-%m-%Y %H:%i') as lastLoginAt"
            )
            ->where('u.deletedAt IS NULL');

        // Field search
        $fields = [
            'u.id',
            'u.name',
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

    /**
     * @param int $year
     * @param string $role
     * @return mixed
     */
    public function getUsersByMonthAndYear(int $year, string $role): mixed
    {
        return $this->createQueryBuilder('entity')
            ->select('YEAR(entity.createdAt) as year, MONTH(entity.createdAt) as month, COUNT(entity.id) as count')
            ->where('YEAR(entity.createdAt) = :year')
            ->andWhere('entity.roles LIKE :role')
            ->andWhere('entity.deletedAt IS NULL')
            ->andWhere('entity.enabled = 1')
            ->setParameter('year', $year)
            ->setParameter('role', '%"' . $role . '"%')
            ->groupBy('year, month')
            ->orderBy('year, month')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getUserYears(): array
    {
        return $this->createQueryBuilder('entity')
            ->select('DISTINCT YEAR(entity.createdAt) as year')
            ->where('entity.roles NOT LIKE :role')
            ->andWhere('entity.deletedAt IS NULL')
            ->andWhere('entity.enabled = 1')
            ->setParameter('role', '%"' . User::ROLE_ADMIN . '"%')
            ->orderBy('year', 'desc')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return $this->createQueryBuilder('entity')
            ->select('entity.id, entity.name, entity.surname')
            ->where('entity.roles NOT LIKE :role')
            ->andWhere('entity.deletedAt IS NULL')
            ->setParameter('role', '%"' . User::ROLE_ADMIN . '"%')
            ->orderBy('entity.id', 'desc')
            ->getQuery()
            ->getResult();
    }
}
