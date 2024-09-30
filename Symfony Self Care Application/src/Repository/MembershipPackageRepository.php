<?php

namespace App\Repository;

use App\Entity\Language;
use App\Entity\MembershipPackage;
use App\Helper\DefaultHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembershipPackage>
 *
 * @method MembershipPackage|null find($id, $lockMode = null, $lockVersion = null)
 * @method MembershipPackage|null findOneBy(array $criteria, array $orderBy = null)
 * @method MembershipPackage[]    findAll()
 * @method MembershipPackage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembershipPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipPackage::class);
    }

    /**
     * @return float|bool|int|string|null
     */
    public function countMembershipPackages(): float|bool|int|string|null
    {
        return $this->createQueryBuilder('mp')
            ->select('COUNT(mp.id)')
            ->where('mp.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $column
     * @param string $dir
     * @param $keyword
     * @param Language $language
     * @return array
     */
    public function findPackagesByFilters(string $column, string $dir, $keyword, Language $language): array
    {
        $queryBuilder = $this->createQueryBuilder('mp')
            ->join('mp.membershipPackageTranslations', 'tr')
            ->select("
                mp.id,
                mp.uuid,
                tr.name,
                mp.slug,
                mp.price,
                mp.discount,
                mp.fileName,
                mp.status,
                DATE_FORMAT(mp.createdAt, '%d-%m-%Y %H:%i') as createdAt"
            )
            ->where('mp.deletedAt IS NULL')
            ->andWhere('tr.language = :language')
            ->setParameter('language', $language);

        // Field search
        $fields = [
            'mp.id',
            'tr.name',
            'mp.status',
            'mp.slug'
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
            ->orderBy($column === 'name' ? 'tr.' . $column : 'mp.' . $column, $dir)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getAllPackages(): array
    {
        return $this->createQueryBuilder('mp')
            ->where('mp.deletedAt IS NULL')
            ->andWhere('mp.status = :status')
            ->setParameter('status', DefaultHelper::STATUS_PUBLISHED)
            ->getQuery()
            ->getResult();
    }
}
