<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChatQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatQuestionRepository::class)]
class ChatQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $question;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToOne(targetEntity: ChatResponse::class, mappedBy: 'question', cascade: ['persist', 'remove'])]
    private ?ChatResponse $response = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
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

    public function getResponse(): ?ChatResponse
    {
        return $this->response;
    }

    public function setResponse(?ChatResponse $response): self
    {
        // unset the owning side of the relation if necessary
        if ($response === null && $this->response !== null) {
            $this->response->setQuestion(null);
        }

        // set the owning side of the relation if necessary
        if ($response !== null && $response->getQuestion() !== $this) {
            $response->setQuestion($this);
        }

        $this->response = $response;
        return $this;
    }
}
