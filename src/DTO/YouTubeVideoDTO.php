<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Video;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Video::class)]
final class YouTubeVideoDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Video ID cannot be blank')]
        public readonly string $youtubeId,
        #[Assert\NotBlank(message: 'Title cannot be blank')]
        public readonly string $title,
        public readonly ?string $description,
        #[Assert\Url(message: 'Thumbnail URL must be a valid URL')]
        #[Assert\NotBlank(message: 'Thumbnail URL cannot be blank')]
        public readonly string $thumbnail,
        #[Assert\NotNull(message: 'Published date cannot be null')]
        public readonly \DateTimeImmutable $publishedAt,
    ) {}

    public static function fromApiResponse(array $videoData): self
    {
        $youtubeId = $videoData['id'];
        $title = $videoData['snippet']['title'];
        $description = $videoData['snippet']['description'] ?? null;
        $thumbnail = $videoData['snippet']['thumbnails']['default']['url'];
        $publishedAt = new \DateTimeImmutable;
        if (isset($videoData['snippet']['publishedAt'])) {
            try {
                $publishedAt = new \DateTimeImmutable($videoData['snippet']['publishedAt']);
            } catch (\Exception $e) {
                $publishedAt = new \DateTimeImmutable;
            }
        }

        return new self(
            $youtubeId,
            $title,
            $description,
            $thumbnail,
            $publishedAt,
        );
    }
}
