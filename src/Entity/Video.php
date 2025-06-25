<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $thumbnail;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public string $youtubeId;

    #[ORM\OneToMany(targetEntity: TranscriptionChunk::class, mappedBy: 'video', cascade: ['persist', 'remove'])]
    private Collection $chunks;

    public function __construct()
    {
        $this->chunks = new ArrayCollection;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getYoutubeId(): string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(string $youtubeId): self
    {
        $this->youtubeId = $youtubeId;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @return Collection<int, TranscriptionChunk>
     */
    public function getChunks(): Collection
    {
        return $this->chunks;
    }

    public function addChunk(TranscriptionChunk $chunk): self
    {
        if (!$this->chunks->contains($chunk)) {
            $this->chunks->add($chunk);
            $chunk->setVideo($this);
        }

        return $this;
    }

    public function removeChunk(TranscriptionChunk $chunk): self
    {
        if ($this->chunks->removeElement($chunk)) {
            // set the owning side to null (unless already changed)
            if ($chunk->getVideo() === $this) {
                $chunk->setVideo(null);
            }
        }

        return $this;
    }
}
