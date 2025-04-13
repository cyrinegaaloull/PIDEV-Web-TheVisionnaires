<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(name: "lieuID", type: "integer")]
    private ?int $lieuid = null;

    #[ORM\Column(name: "userID", type: "integer")]
    private ?int $userid = null;

    #[ORM\Column(name: "rating", type: "float", precision: 10, scale: 0)]
    private ?float $rating = null;

    #[ORM\Column(name: "comment", type: "string", length: 50, options: ["default" => "aucun commentaire"])]
    private string $comment = 'aucun commentaire';

    #[ORM\Column(name: "reviewDate", type: "date", nullable: true)]
    private ?\DateTimeInterface $reviewdate = null;

    // --- Getters & Setters ---

    public function getReviewid(): ?int
    {
        return $this->reviewid;
    }

    public function getLieuid(): ?int
    {
        return $this->lieuid;
    }

    public function setLieuid(?int $lieuid): self
    {
        $this->lieuid = $lieuid;
        return $this;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(?int $userid): self
    {
        $this->userid = $userid;
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
