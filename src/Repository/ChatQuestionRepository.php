<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChatQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatQuestion>
 *
 * @method ChatQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatQuestion[]    findAll()
 * @method ChatQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatQuestion::class);
    }

    /**
     * Find recent questions with their responses
     */
    public function findRecentQuestionsWithResponses(int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.response', 'r')
            ->orderBy('q.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
