<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TranscriptionChunkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranscriptionChunkRepository::class)]
class TranscriptionChunk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Video::class, inversedBy: 'chunks')]
    #[ORM\JoinColumn(nullable: false)]
    private Video $video;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::INTEGER)]
    private int $videoOffset;

    #[ORM\Column(type: Types::INTEGER)]
    private int $duration;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $lang;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $embedding = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function setVideo(Video $video): self
    {
        $this->video = $video;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getVideoOffset(): int
    {
        return $this->videoOffset;
    }

    public function setVideoOffset(int $videoOffset): self
    {
        $this->videoOffset = $videoOffset;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function getEmbedding(): ?array
    {
        return $this->embedding;
    }

    public function setEmbedding(?array $embedding): self
    {
        $this->embedding = $embedding;
        return $this;
    }

    /**
     * Get a YouTube URL that points directly to this timestamp in the video
     */
    public function getYouTubeTimestampUrl(): string
    {
        $seconds = floor($this->videoOffset / 1000);
        return 'https://www.youtube.com/watch?v=' . $this->video->getYoutubeId() . '&t=' . $seconds . 's';
    }
}
