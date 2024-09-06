<?php

namespace App\Repository;

use App\Entity\User;
use App\Helper\DefaultHelper;
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
    /**
     * @var DefaultHelper
     */
    protected DefaultHelper $helper;

    /**
     * @param ManagerRegistry $registry
     * @param DefaultHelper $helper
     */
    public function __construct(ManagerRegistry $registry, DefaultHelper $helper)
    {
        parent::__construct($registry, User::class);
        $this->helper = $helper;
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
     * @return array
     */
    public function findUsersByFilters(string $column, string $dir, $keyword): array
    {
        $defaultImage = $this->helper->getEnvValue('app_default_image');

        $queryBuilder = $this->createQueryBuilder('u')
            ->select("
                u.id,
                u.uuid,
                u.name,
                u.roles,
                COALESCE(u.profilePicture, '$defaultImage') as fileName,
                u.email,
                u.enabled,
                DATE_FORMAT(u.createdAt, '%d-%m-%Y') as createdAt,
                DATE_FORMAT(u.lastLoginAt, '%d-%m-%Y %H:%i') as lastLoginAt"
            )
            ->where('u.deletedAt IS NULL')
            ->andWhere("u.roles NOT LIKE '%ROLE_ADMIN%'");

        // Field search
        $fields = [
            'u.id',
            'u.name',
            'u.surname',
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
