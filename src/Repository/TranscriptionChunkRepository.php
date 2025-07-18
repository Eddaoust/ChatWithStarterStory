<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TranscriptionChunk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TranscriptionChunk>
 *
 * @method TranscriptionChunk|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranscriptionChunk|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranscriptionChunk[]    findAll()
 * @method TranscriptionChunk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranscriptionChunkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranscriptionChunk::class);
    }
}
