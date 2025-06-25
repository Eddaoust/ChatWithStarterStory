<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\YouTubeVideoDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YouTubeService
{
    private const YOUTUBE_API_URL = 'https://www.googleapis.com/youtube/v3/videos';
    private const MAX_IDS_PER_REQUEST = 50;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    public function getVideoMetadata(string $videoId): YouTubeVideoDTO
    {
        try {
            $response = $this->httpClient->request('GET', self::YOUTUBE_API_URL, [
                'query' => [
                    'part' => 'snippet',
                    'id' => $videoId,
                    'key' => $this->apiKey,
                ]
            ]);

            $data = $response->toArray();

            if (empty($data['items'])) {
                throw new \Exception('Video not found or is unavailable');
            }

            $videoData = $data['items'][0];

            return YouTubeVideoDTO::fromApiResponse($videoData);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching video metadata: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getMultipleVideosMetadata(array $videoIds): array
    {
        if (count($videoIds) > self::MAX_IDS_PER_REQUEST) {
            throw new \InvalidArgumentException(
                sprintf('YouTube API can only handle a maximum of %d video IDs per request', self::MAX_IDS_PER_REQUEST)
            );
        }

        try {
            $response = $this->httpClient->request('GET', self::YOUTUBE_API_URL, [
                'query' => [
                    'part' => 'snippet',
                    'id' => implode(',', $videoIds), // YouTube API allows multiple IDs separated by commas
                    'key' => $this->apiKey,
                ]
            ]);

            $data = $response->toArray();

            if (empty($data['items'])) {
                return [];
            }

            $result = [];
            foreach ($data['items'] as $videoData) {
                $videoId = $videoData['id'];
                $result[$videoId] = YouTubeVideoDTO::fromApiResponse($videoData);
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching videos metadata: ' . $e->getMessage(), 0, $e);
        }
    }
}
