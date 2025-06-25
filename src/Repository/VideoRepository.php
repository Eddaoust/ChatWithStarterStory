<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function findAllYoutubeIds(): array
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->select('v.id', 'v.youtubeId')
            ->where('v.chunks IS EMPTY');

        $result = $queryBuilder->getQuery()->getArrayResult();

        $youtubeIds = [];
        foreach ($result as $item) {
            $youtubeIds[$item['id']] = $item['youtubeId'];
        }

        return $youtubeIds;
    }
}
