<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\LieuRepository;
#[ORM\Entity(repositoryClass: LieuRepository::class)]
#[ORM\Table(name: "lieu")]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "lieuID", type: "integer")]
    private ?int $lieuid = null;
    #[ORM\Column(name: "lieuName", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le nom du lieu est requis.")]
    #[Assert\Length(min: 3, max: 100)]
    private ?string $lieuname = null;

    #[ORM\Column(name: "lieuAddress", type: "string", length: 100)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères.",
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $lieuaddress = null;

    #[ORM\Column(name: "lieuDescription", type: "string", length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $lieudescription = null;

    #[ORM\Column(name: "lieuCategory", type: "string", length: 30)]
    #[Assert\NotBlank(message: "La catégorie est requise.")]
    private ?string $lieucategory = null;

    #[ORM\Column(name: "lieuOpeningHours", type: "text")]
    #[Assert\NotBlank(message: "L'heure d'ouverture est requise.")]
    private ?string $lieuopeninghours = null;

    #[ORM\Column(name: "lieuClosingHours", type: "text")]
    #[Assert\NotBlank(message: "L'heure de fermeture est requise.")]
    private ?string $lieuclosinghours = null;

    #[ORM\Column(name: "lieuNumber", type: "integer", nullable: true)]
    private ?int $lieunumber = null;

    #[ORM\Column(name: "lieuImage", type: "string", length: 20, nullable: true)]
    private ?string $lieuimage = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $isfavorite = false;

    #[Assert\Callback]
    public static function validate(self $object, ExecutionContextInterface $context, $payload = null): void
    {
        if ($object->lieunumber !== null) {
            $len = strlen((string)$object->lieunumber);
            if ($len !== 8) {
                $context->buildViolation('Le numéro du lieu doit contenir exactement 8 chiffres.')
                    ->atPath('lieunumber')
                    ->addViolation();
            }
        }
    }
    public function getLieuid(): ?int
    {
        return $this->lieuid;
    }

    public function getLieuname(): ?string
    {
        return $this->lieuname;
    }

    public function setLieuname(?string $lieuname): self
    {
        $this->lieuname = $lieuname;
        return $this;
    }

    public function getLieuaddress(): ?string
    {
        return $this->lieuaddress;
    }

    public function setLieuaddress(?string $lieuaddress): self
    {
        $this->lieuaddress = $lieuaddress;
        return $this;
    }

    public function getLieudescription(): ?string
    {
        return $this->lieudescription;
    }

    public function setLieudescription(?string $lieudescription): self
    {
        $this->lieudescription = $lieudescription;
        return $this;
    }

    public function getLieucategory(): ?string
    {
        return $this->lieucategory;
    }

    public function setLieucategory(?string $lieucategory): self
    {
        $this->lieucategory = $lieucategory;
        return $this;
    }

    public function getLieuopeninghours(): ?string
    {
        return $this->lieuopeninghours;
    }

    public function setLieuopeninghours(?string $lieuopeninghours): self
    {
        $this->lieuopeninghours = $lieuopeninghours;
        return $this;
    }

    public function getLieuclosinghours(): ?string
    {
        return $this->lieuclosinghours;
    }

    public function setLieuclosinghours(?string $lieuclosinghours): self
    {
        $this->lieuclosinghours = $lieuclosinghours;
        return $this;
    }

    public function getLieunumber(): ?int
    {
        return $this->lieunumber;
    }

    public function setLieunumber(?int $lieunumber): self
    {
        $this->lieunumber = $lieunumber;
        return $this;
    }

    public function getLieuimage(): ?string
    {
        return $this->lieuimage;
    }

    public function setLieuimage(?string $lieuimage): self
    {
        $this->lieuimage = $lieuimage;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getIsfavorite(): ?bool
    {
        return $this->isfavorite;
    }

    public function setIsfavorite(?bool $isfavorite): self
    {
        $this->isfavorite = $isfavorite;
        return $this;
    }
}
