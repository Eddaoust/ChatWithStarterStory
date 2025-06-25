<?php

declare(strict_types=1);

namespace App\Service;

use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\OpenAIConfig;

class EmbeddingService
{
    private readonly OpenAI3SmallEmbeddingGenerator $embeddingGenerator;

    public function __construct(string $apiKey)
    {
        $config = new OpenAIConfig;
        $config->apiKey = $apiKey;
        $this->embeddingGenerator = new OpenAI3SmallEmbeddingGenerator($config);
    }

    public function generateEmbedding(string $text): array
    {
        $embeddings = $this->embeddingGenerator->embedText($text);

        return $embeddings;
    }
}
