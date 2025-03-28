<?php


namespace App\Entity;
use App\Entity\Categorie; 

use Doctrine\ORM\Mapping as ORM;

/**
 * Etablissement
 *
 * @ORM\Table(name="etablissement", indexes={@ORM\Index(name="fk_categorie", columns={"categoryID"})})
 * @ORM\Entity
 */
class Etablissement
{
    /**
     * @var int
     *
     * @ORM\Column(name="etabID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $etabid;

    /**
     * @var string
     *
     * @ORM\Column(name="etabName", type="string", length=50, nullable=false)
     */
    private $etabname;

    /**
     * @var string
     *
     * @ORM\Column(name="etabAddress", type="string", length=255, nullable=false)
     */
    private $etabaddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="etabHoraire", type="string", length=0, nullable=true, options={"default"="HORAIRE_8_17"})
     */
    private $etabhoraire = 'HORAIRE_8_17';

    /**
     * @var string|null
     *
     * @ORM\Column(name="region", type="string", length=50, nullable=true)
     */
    private $region;

    /**
     * @var string|null
     *
     * @ORM\Column(name="geolocation", type="string", length=50, nullable=true)
     */
    private $geolocation;

    /**
     * @var Categorie
     *
     * @ORM\ManyToOne(targetEntity="Categorie")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="categoryID", referencedColumnName="categoryID")
     * })
     */
    private $categoryid;


    /**
     * Get etabid.
     *
     * @return int
     */
    public function getEtabid()
    {
        return $this->etabid;
    }

    /**
     * Set etabname.
     *
     * @param string $etabname
     *
     * @return Etablissement
     */
    public function setEtabname($etabname)
    {
        $this->etabname = $etabname;

        return $this;
    }

    /**
     * Get etabname.
     *
     * @return string
     */
    public function getEtabname()
    {
        return $this->etabname;
    }

    /**
     * Set etabaddress.
     *
     * @param string $etabaddress
     *
     * @return Etablissement
     */
    public function setEtabaddress($etabaddress)
    {
        $this->etabaddress = $etabaddress;

        return $this;
    }

    /**
     * Get etabaddress.
     *
     * @return string
     */
    public function getEtabaddress()
    {
        return $this->etabaddress;
    }

    /**
     * Set etabhoraire.
     *
     * @param string|null $etabhoraire
     *
     * @return Etablissement
     */
    public function setEtabhoraire($etabhoraire = null)
    {
        $this->etabhoraire = $etabhoraire;

        return $this;
    }

    /**
     * Get etabhoraire.
     *
     * @return string|null
     */
    public function getEtabhoraire()
    {
        return $this->etabhoraire;
    }

    /**
     * Set region.
     *
     * @param string|null $region
     *
     * @return Etablissement
     */
    public function setRegion($region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region.
     *
     * @return string|null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set geolocation.
     *
     * @param string|null $geolocation
     *
     * @return Etablissement
     */
    public function setGeolocation($geolocation = null)
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    /**
     * Get geolocation.
     *
     * @return string|null
     */
    public function getGeolocation()
    {
        return $this->geolocation;
    }

    /**
     * Set categoryid.
     *
     * @param Categorie|null $categoryid
     *
     * @return Etablissement
     */
    public function setCategoryid(Categorie $categoryid = null)
    {
        $this->categoryid = $categoryid;

        return $this;
    }

    /**
     * Get categoryid.
     *
     * @return Categorie|null
     */
    public function getCategoryid()
    {
        return $this->categoryid;
    }
}
