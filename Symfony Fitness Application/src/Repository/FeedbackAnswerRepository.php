<?php

namespace App\Repository;

use App\Entity\FeedbackAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedbackAnswer>
 *
 * @method FeedbackAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackAnswer[]    findAll()
 * @method FeedbackAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackAnswer::class);
    }

}
