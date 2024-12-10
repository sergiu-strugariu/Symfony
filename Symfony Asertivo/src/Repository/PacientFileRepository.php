<?php

namespace App\Repository;

use App\Entity\PacientFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacientFile>
 *
 * @method PacientFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method PacientFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method PacientFile[]    findAll()
 * @method PacientFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacientFileRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PacientFile::class);
    }

    public function add(PacientFile $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PacientFile $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFilesByPacient($pacient): array {
        return $this->createQueryBuilder('pf')
                        ->where('pf.pacient = :pacient')
                        ->andWhere('pf.deletedAt IS NULL')
                        ->setParameter('pacient', $pacient)
                        ->orderBy('pf.id', 'ASC')
                        ->getQuery()
                        ->getResult()
        ;
    }

    public function findFilesCountByPacientIdAndStatus($pacientId, $status = null) {
        $queryBuilder = $this->createQueryBuilder('pf')
                ->select('COUNT(pf)')
                ->join('pf.pacient', 'p')
                ->where('p.id = :id')
                ->setParameter('id', $pacientId);

        if (null !== $status) {
            $queryBuilder->andWhere('pf.status = :status')
                    ->setParameter('status', $status);
        }

        return $queryBuilder->getQuery()
                        ->getSingleScalarResult();
    }

    public function findDocumentsForDatatable($type) {
        $data = $this->createQueryBuilder('pf')
                ->select('pf.fileName, pf.fileNumber, pf.fileDate, pf.fileDetails, u.firstName AS uploaderFirstName, u.lastName AS uploaderLastName, ur.firstName AS responsibleFirstName, ur.lastName AS responsibleLastName, pf.status, pf.views')
                ->leftJoin('pf.fileType', 'pft')
                ->leftJoin('pf.addedBy', 'u')
                ->leftJoin('pf.userResponsible', 'ur')
                ->where('pft.id = :type')
                ->setParameter('type', $type)
                ->getQuery()
                ->getArrayResult();

        return array_map(function ($item) {
            // reformat fields
            $item['fileDate'] = ($item['fileDate'] instanceof \DateTime) ? $item['fileDate']->format('d-m-Y') : '-';
            $item['uploaderName'] = (empty($item['uploaderFirstName']) && empty($item['uploaderLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['uploaderFirstName'], $item['uploaderLastName']);
            $item['responsibleName'] = (empty($item['responsibleFirstName']) && empty($item['responsibleLastName'])) ? 'Fara nume' : sprintf('%s %s', $item['responsibleFirstName'], $item['responsibleLastName']);

            return $item;
        }, $data);
    }

    public function findPacientFilesByResponsibleUser($user, $deadline, $month, $year, $count = false) {
        $queryBuilder = $this->createQueryBuilder('pf')
                ->where('pf.userResponsible = :user')
                ->andWhere('pf.status = :status')
                ->setParameter('user', $user)
                ->setParameter('status', PacientFile::STATUS_WAITING);

        if ($deadline) {
            $queryBuilder->andWhere('pf.uploadDeadline IS NOT NULL')
                    ->andWhere('MONTH(pf.uploadDeadline) = :month')
                    ->andWhere('YEAR(pf.uploadDeadline) = :year')
                    ->setParameter('month', $month)
                    ->setParameter('year', $year);
        } else {
            $queryBuilder->andWhere('pf.uploadDeadline IS NULL');
        }

        if ($count) {
            return $queryBuilder->select('COUNT(pf)')
                            ->getQuery()
                            ->getSingleScalarResult();
        }

        return $queryBuilder->getQuery()
                        ->getResult();
    }

    public function findPacientFilesDeadlinesByResponsibleUser($user, $month, $year) {
        $results = $this->createQueryBuilder('pf')
                ->select("DATE_FORMAT(pf.uploadDeadline, '%d/%m/%Y') as uploadDeadline")
                ->where('pf.userResponsible = :user')
                ->andWhere('pf.status = :status')
                ->andWhere('pf.uploadDeadline IS NOT NULL')
                ->andWhere('MONTH(pf.uploadDeadline) = :month')
                ->andWhere('YEAR(pf.uploadDeadline) = :year')
                ->setParameter('user', $user)
                ->setParameter('month', $month)
                ->setParameter('year', $year)
                ->setParameter('status', PacientFile::STATUS_WAITING)
                ->orderBy('uploadDeadline', 'DESC')
                ->groupBy('uploadDeadline')
                ->getQuery()
                ->getArrayResult();

        return array_column($results, 'uploadDeadline');
    }

}
