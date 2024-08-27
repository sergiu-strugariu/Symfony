<?php

namespace App\Repository;

use App\Entity\FeedbackQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedbackQuestion>
 *
 * @method FeedbackQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackQuestion[]    findAll()
 * @method FeedbackQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackQuestion::class);
    }

}
