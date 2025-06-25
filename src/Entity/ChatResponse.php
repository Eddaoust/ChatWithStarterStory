<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChatResponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatResponseRepository::class)]
class ChatResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: ChatQuestion::class, inversedBy: 'response')]
    #[ORM\JoinColumn(nullable: false)]
    private ChatQuestion $question;

    #[ORM\Column(type: Types::TEXT)]
    private string $answer;

    #[ORM\Column(type: Types::JSON)]
    private array $relevantVideos = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ChatQuestion
    {
        return $this->question;
    }

    public function setQuestion(ChatQuestion $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;
        return $this;
    }

    public function getRelevantVideos(): array
    {
        return $this->relevantVideos;
    }

    public function setRelevantVideos(array $relevantVideos): self
    {
        $this->relevantVideos = $relevantVideos;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
