<?php

declare(strict_types=1);

namespace App\Service;


use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;

class RAGService
{
    public function __construct(
        private readonly VectorSearchService $vectorSearchService,
        private readonly string $apiKey
    ) {}

    public function generateResponse(string $userQuestion): array
    {
        $relevantChunks = $this->vectorSearchService->searchRelevantChunks($userQuestion);
        $relevantVideos = $this->vectorSearchService->getUniqueVideosFromChunks($relevantChunks);

        $context = $this->buildContext($relevantChunks);

        $response = $this->queryLLM($userQuestion, $context);

        return [
            'answer' => $response,
            'videos' => str_starts_with($response, 'I don\'t know') ? [] : array_map(function($item) {
                $video = $item['video'];
                $chunk = $item['relevantChunk'];

                return [
                    'id' => $video->getId(),
                    'title' => $video->getTitle(),
                    'thumbnail' => $video->getThumbnail(),
                    'youtubeId' => $video->getYoutubeId(),
                    'timestampUrl' => $chunk->getYouTubeTimestampUrl(),
                ];
            }, $relevantVideos)
        ];

    }

    private function buildContext(array $chunks): string
    {
        $context = "Here are some relevant video extracts :\n\n";

        foreach ($chunks as $index => $chunk) {
            $context .= "Extract " . ($index + 1) . " (from video \"" . $chunk->getVideo()->getTitle() . "\") :\n";
            $context .= $chunk->getContent() . "\n\n";
        }

        return $context;
    }

    private function queryLLM(string $question, string $context): string
    {
        $config = new OpenAIConfig;
        $config->model = 'o4-mini-2025-04-16';
        $config->apiKey = $this->apiKey;
        //$config->modelOptions['temperature'] = 0;

        $chat = new OpenAIChat($config);

        $prompt = <<<PROMPT
        Context :
        $context

        Question :
        $question

        Based on the above context, answer the question clearly and concisely. If the context does not allow you to answer, return “I don't know”..
        PROMPT;

        return $chat->generateText($prompt);
    }
}
