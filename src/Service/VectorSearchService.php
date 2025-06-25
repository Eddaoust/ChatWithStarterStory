<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TranscriptionChunk;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class VectorSearchService
{
    public function __construct(
        private readonly TransformedFinder $transcriptionChunksFinder,
        private readonly EmbeddingService  $embeddingService
    ) {}

    public function searchRelevantChunks(string $question, int $limit = 5): array
    {
        $questionEmbedding = $this->embeddingService->generateEmbedding($question);

        $query = [
            'knn' => [
                'field' => 'embedding',
                'query_vector' => $questionEmbedding,
                'k' => $limit,
                'num_candidates' => $limit * 3
            ]
        ];

        return $this->transcriptionChunksFinder->find($query);
    }

    public function getUniqueVideosFromChunks(array $chunks, int $limit = 3): array
    {
        $uniqueVideos = [];

        foreach ($chunks as $chunk) {
            if ($chunk instanceof TranscriptionChunk) {
                $video = $chunk->getVideo();
                $videoId = $video->getId();

                if (!isset($uniqueVideos[$videoId])) {
                    $uniqueVideos[$videoId] = [
                        'video' => $video,
                        'relevantChunk' => $chunk
                    ];

                    if (count($uniqueVideos) >= $limit) {
                        break;
                    }
                }
            }
        }

        return array_values($uniqueVideos);
    }
}
