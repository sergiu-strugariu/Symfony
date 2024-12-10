<?php

namespace App\Repository;

use App\Entity\Nps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nps>
 *
 * @method Nps|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nps|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nps[]    findAll()
 * @method Nps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NpsRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Nps::class);
    }

    public function add(Nps $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Nps $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findNpsByRelationAndPacientUuids($relationUuid, $pacientUuid) {
        return $this->createQueryBuilder('nps')
                        ->join('nps.pacient', 'p')
                        ->join('nps.user', 'u')
                        ->where('p.uid = :pacientUuid')
                        ->andWhere('u.uid = :relationUuid')
                        ->setParameter('pacientUuid', $pacientUuid)
                        ->setParameter('relationUuid', $relationUuid)
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

}
