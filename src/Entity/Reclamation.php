<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'reclamation')]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'reclamation_id', type: 'integer')]
    private ?int $reclamationId = null;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: false)]
    private int $userId;

    #[ORM\Column(name: 'post_id', type: 'integer', nullable: false)]
    private int $postId;

    #[ORM\Column(name: 'content', type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu de la réclamation est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le contenu doit comporter au moins {{ limit }} caractères.'
    )]
    private string $content;

    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->status = 'pending'; // Default status
        $this->createdAt = new \DateTime();
    }

    public function getReclamationId(): ?int
    {
        return $this->reclamationId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}