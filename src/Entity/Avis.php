<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Etablissement;
use App\Entity\Users;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'avis')]
#[ORM\Index(name: 'etabID', columns: ['etabID'])]
#[ORM\Index(name: 'userID', columns: ['userID'])]
#[ORM\Entity]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'avisID', type: 'integer', nullable: false)]
    private ?int $avisid = null;

    #[ORM\ManyToOne(targetEntity: Etablissement::class)]
    #[ORM\JoinColumn(name: 'etabID', referencedColumnName: 'etabID', nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'userID', referencedColumnName: 'user_id', nullable: false)] // Fix: Match the column name in Users entity
    private ?Users $user = null;

    #[ORM\Column(name: 'rating', type: 'integer', nullable: false)]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: 'La note doit être entre {{ min }} et {{ max }} étoiles.'
    )]
    private ?int $rating = null;

    #[ORM\Column(name: 'dateAvis', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $dateavis = null;

    public function getAvisid(): ?int
    {
        return $this->avisid;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getDateavis(): ?\DateTimeInterface
    {
        return $this->dateavis;
    }

    public function setDateavis(\DateTimeInterface $dateavis): self
    {
        $this->dateavis = $dateavis;
        return $this;
    }

    public function getEtabid(): ?int
    {
        return $this->etablissement ? $this->etablissement->getEtabid() : null;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }
}
