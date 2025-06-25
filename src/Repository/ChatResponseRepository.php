<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChatResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatResponse>
 *
 * @method ChatResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatResponse[]    findAll()
 * @method ChatResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatResponse::class);
    }
}
