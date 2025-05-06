<?php


namespace App\Entity; 
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Etablissement;

#[ORM\Table(name: 'avis')]
#[ORM\Index(name: 'etabID', columns: ['etabID'])]
#[ORM\Index(name: 'userID', columns: ['userID'])]
#[ORM\Entity]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'avisID', type: 'integer', nullable: false)]
    private $avisid;


    #[ORM\ManyToOne(targetEntity: Etablissement::class)]
    #[ORM\JoinColumn(name: 'etabID', referencedColumnName: 'etabID', nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(name: 'etabID', type: 'integer', nullable: false)]
    private $etabid;


    #[ORM\Column(name: 'userID', type: 'integer', nullable: false)]
    private $userid;

    #[ORM\Column(name: 'rating', type: 'integer', nullable: false)]
    private $rating;

    #[ORM\Column(name: 'dateAvis', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $dateavis = 'CURRENT_TIMESTAMP';

    /**
     * Get avisid.
     *
     * @return int
     */
    public function getAvisid()
    {
        return $this->avisid;
    }

    /**
     * Get userid.
     *
     * @return int
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set userid.
     *
     * @param int $userid
     *
     * @return Avis
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
        return $this;
    }

    /**
     * Get rating.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set rating.
     *
     * @param int $rating
     *
     * @return Avis
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
        return $this;
    }

    /**
     * Get dateavis.
     *
     * @return \DateTime
     */
    public function getDateavis()
    {
        return $this->dateavis;
    }

    /**
     * Set dateavis.
     *
     * @param \DateTime $dateavis
     *
     * @return Avis
     */
    public function setDateavis($dateavis)
    {
        $this->dateavis = $dateavis;
        return $this;
    }

    // Méthode pour compatibilité avec les anciennes requêtes
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
