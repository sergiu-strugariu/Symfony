<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

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

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    /**
     * @param User $user
     * @param string $role
     * @return array
     */
    public function findUsersByRole(User $user, string $role): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select("u.id, u.photo, u.firstName, u.lastName, u.jobName, u.status, u.email, u.phoneNumber, u.cnp, DATE_FORMAT(u.createdAt, '%d/%m/%Y') as createdAt, nh.name as nursingHome, u.dateOfBirth, u.uid")
            ->join('u.nursingHome', 'nh')
            ->where("u.roles LIKE :role")
            ->setParameter('role', '%' . addcslashes($role, '%_') . '%');

        if ($user->hasRole('ROLE_ADMIN')) {
            $queryBuilder
                ->andWhere('nh.id IN (:nursingHomes)')
                ->setParameter('nursingHomes', $user->getNursingHomes());
        } else {
            $queryBuilder
                ->andWhere('nh.id = :id')
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

    public function searchUsers($nursingHome, $query, $role = null, $status = null)
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u.photo, u.firstName, u.lastName, u.email, u.uid')
            ->where("u.firstName LIKE :query OR u.lastName LIKE :query OR u.email LIKE :query")
            ->andWhere("u.nursingHome = :nursingHome")
            ->setParameter('query', '%' . addcslashes($query, '%_') . '%')
            ->setParameter('nursingHome', $nursingHome);

        if (null !== $role) {
            $queryBuilder->andWhere("u.roles LIKE :role")
                ->setParameter('role', '%' . addcslashes($role, '%_') . '%');
        }

        if (null !== $status) {
            $queryBuilder->andWhere("u.status = :status")
                ->setParameter('status', $status);
        }

        $data = $queryBuilder->getQuery()
            ->getArrayResult();

        $package = new UrlPackage(User::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());
        return (empty($this->photo)) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $this->photo));


        return array_map(function ($item) use ($package) {
            // reformat datetime
            $item['name'] = (empty($item['firstName']) && empty($item['lastName'])) ? 'Fara nume' : sprintf('%s %s', $item['firstName'], $item['lastName']);
            $item['photo'] = (empty($item['photo'])) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $item['photo']));

            return $item;
        }, $data);
    }

    public function findUsersCountByStatus($role, $status)
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where("u.roles LIKE :role")
            ->andWhere('u.status = :status')
            ->setParameter('status', $status)
            ->setParameter('role', '%' . addcslashes($role, '%_') . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAdmins()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();
    }

    public function getUsers()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();
    }
}
