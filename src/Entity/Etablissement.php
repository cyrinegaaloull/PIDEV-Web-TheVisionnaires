<?php

namespace App\Entity;

use App\Repository\EtablissementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtablissementRepository::class)]
#[ORM\Table(name: 'etablissement')]
#[ORM\Index(name: 'fk_categorie', columns: ['categoryID'])]
class Etablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'etabID', type: 'integer')]
    private $etabid;

    #[ORM\Column(name: 'etabName', type: 'string', length: 50)]
    #[Assert\NotBlank(message: "Le nom de l'établissement ne peut pas être vide")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private $etabname = '';

    #[ORM\Column(name: 'etabAddress', type: 'string', length: 255)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères",
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères" 
    )]
    private $etabaddress = '';

    #[ORM\Column(name: 'etabHoraire', type: 'string', length: 50, nullable: true)]
    private $etabhoraire = '';

    /**
 * Propriété non persistée pour manipuler la date
 */
#[Assert\NotNull(message: "La date et l'heure d'ouverture ne peuvent pas être vides")]
#[Assert\LessThanOrEqual(
    value: "today",
    message: "La date ne peut pas être dans le futur"
)]
private ?\DateTime $horaireDateObject = null;

#[ORM\Column(name: 'region', type: 'string', length: 50, nullable: true)]
#[Assert\NotBlank(message: "La région ne peut pas être vide")]
#[Assert\Length(
    max: 50,
    maxMessage: "La région ne peut pas dépasser {{ limit }} caractères"
)]
private $region = '';

#[ORM\Column(name: 'geolocation', type: 'string', length: 50, nullable: true)]
#[Assert\Regex(
    pattern: "/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/",
    message: "Le format de géolocalisation doit être valide (ex: 48.8566, 2.3522)"
)]

    private $geolocation = '';

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(name: 'categoryID', referencedColumnName: 'categoryID')]
    #[Assert\NotNull(message: "Veuillez sélectionner une catégorie")]
    private $categoryid;

    public function getEtabid(): ?int
    {
        return $this->etabid;
    }

    public function getEtabname(): ?string
    {
        return $this->etabname;
    }
     // Convertir null en chaîne vide
    public function setEtabname(?string $etabname): self
    {
        $this->etabname = $etabname === null ? '' : $etabname;

        return $this;
    }
     
    public function getEtabaddress(): ?string
    {
        return $this->etabaddress;
    }

    public function setEtabaddress(?string $etabaddress): self
    {
        $this->etabaddress = $etabaddress === null ? '' : $etabaddress;

        return $this;
    }

    public function getEtabhoraire(): ?string
    {
        return $this->etabhoraire;
    }

    public function setEtabhoraire(?string $etabhoraire): self
    {
        $this->etabhoraire = $etabhoraire === null ? '' : $etabhoraire;

        return $this;
    }

    /**
     * Obtenir l'objet DateTime à partir de la chaîne stockée
     */
    public function getHoraireDateObject(): ?\DateTime
    {
        if ($this->horaireDateObject === null && $this->etabhoraire) {
            try {
                $this->horaireDateObject = new \DateTime($this->etabhoraire);
            } catch (\Exception $e) {
                // La chaîne stockée n'est pas une date valide
            }
        }
        return $this->horaireDateObject;
    }

    /**
     * Définir la date et mettre à jour la chaîne stockée
     */
    public function setHoraireDateObject(?\DateTime $date): self
    {
        $this->horaireDateObject = $date;
        if ($date !== null) {
            $this->etabhoraire = $date->format('Y-m-d H:i:s');
        }
        return $this;
    }

    /**
     * Formater la date pour l'affichage
     */
    public function getHoraireFormatted(): string
    {
        if ($this->getHoraireDateObject()) {
            return $this->getHoraireDateObject()->format('d/m/Y H:i');
        }
        return $this->etabhoraire ?? '';
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region === null ? '' : $region;

        return $this;
    }

    public function getGeolocation(): ?string
    {
        return $this->geolocation;
    }

    public function setGeolocation(?string $geolocation): self
    {
        $this->geolocation = $geolocation === null ? '' : $geolocation;

        return $this;
    }

    public function getCategoryid(): ?Categorie
    {
        return $this->categoryid;
    }

    public function setCategoryid(?Categorie $categoryid): self
    {
        $this->categoryid = $categoryid;

        return $this;
    }
}
