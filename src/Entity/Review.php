<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "review")]
#[ORM\Index(name: "lieuID", columns: ["lieuID"])]
#[ORM\Index(name: "userID", columns: ["userID"])]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "reviewID", type: "integer")]
    private ?int $reviewid = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
#[ORM\JoinColumn(name: "lieuID", referencedColumnName: "lieuID", onDelete: "CASCADE")]
private ?Lieu $lieuid = null;

#[ORM\ManyToOne(targetEntity: Users::class)]
#[ORM\JoinColumn(name: "userID", referencedColumnName: "user_id", onDelete: "CASCADE")]
private ?Users $user = null;

    #[ORM\Column(name: "rating", type: "float", precision: 10, scale: 0)]
    #[Assert\NotBlank(message: 'Veuillez attribuer une note.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être entre {{ min }} et {{ max }}.')]
    private ?float $rating = null;

    #[ORM\Column(name: "comment", type: "string", length: 50, options: ["default" => "aucun commentaire"])]
    #[Assert\NotBlank(message: 'Le commentaire ne peut pas être vide.')]
    private string $comment = 'aucun commentaire';

    #[ORM\Column(name: "reviewDate", type: "date", nullable: true)]
    private ?\DateTimeInterface $reviewdate = null;

    // --- Getters & Setters ---

    public function getReviewid(): ?int
    {
        return $this->reviewid;
    }

    public function getLieuid(): ?Lieu
{
    return $this->lieuid;
}

public function setLieuid(?Lieu $lieuid): self
{
    $this->lieuid = $lieuid;
    return $this;
}


    public function getUser(): ?Users
{
    return $this->user;
}

public function setUser(?Users $user): self
{
    $this->user = $user;
    return $this;
}

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getReviewdate(): ?\DateTimeInterface
    {
        return $this->reviewdate;
    }

    public function setReviewdate(?\DateTimeInterface $reviewdate): self
    {
        $this->reviewdate = $reviewdate;
        return $this;
    }
}
