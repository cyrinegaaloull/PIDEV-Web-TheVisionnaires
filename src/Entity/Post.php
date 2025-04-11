<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="post")
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="post_id", type="integer")
     */
    private ?int $postId = null;

    /**
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private ?int $userId = null;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Assert\NotBlank(message="Le titre ne doit pas être vide.")
     * @Assert\Length(
     *      min=3,
     *      max=255,
     *      minMessage="Le titre doit comporter au moins {{ limit }} caractères.",
     *      maxMessage="Le titre ne doit pas dépasser {{ limit }} caractères."
     * )
     */
    private string $title;

    /**
     * @ORM\Column(name="content", type="text")
     *
     * @Assert\NotBlank(message="Le contenu ne doit pas être vide.")
     * @Assert\Length(
     *      min=10,
     *      minMessage="Le contenu doit comporter au moins {{ limit }} caractères."
     * )
     */
    private string $content;

    /**
     * @ORM\Column(name="category", type="string", length=255)
     *
     * @Assert\NotBlank(message="La catégorie est obligatoire.")
     * @Assert\Length(
     *      max=255,
     *      maxMessage="La catégorie ne doit pas dépasser {{ limit }} caractères."
     * )
     */
    private string $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post", cascade={"remove"})
     */
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }
}
