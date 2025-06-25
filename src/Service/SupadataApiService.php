<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SupadataApiService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    public function getYoutubeChannelVideos(string $channelId, int $limit = 30): array
    {
        $response = $this->httpClient->request('GET', 'https://api.supadata.ai/v1/youtube/channel/videos', [
            'headers' => [
                'x-api-key' => $this->apiKey,
            ],
            'query' => [
                'id' => $channelId,
                'limit' => $limit,
                'type' => 'video',
            ]
        ]);

        return $response->toArray();
    }

    public function getYoutubeVideoTranscription(string $videoId, string $lang = 'en'): string
    {
        $response = $this->httpClient->request('GET', 'https://api.supadata.ai/v1/youtube/transcript', [
            'headers' => [
                'x-api-key' => $this->apiKey,
            ],
            'query' => [
                'videoId' => $videoId,
                'lang' => $lang,
                'chunkSize' => 500
            ]
        ]);

        return $response->getContent();
    }
}
