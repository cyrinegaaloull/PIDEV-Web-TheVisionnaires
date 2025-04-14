<?php

namespace App\Entity; 
use Doctrine\ORM\Mapping as ORM;

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
     * Set etabid.
     *
     * @param int $etabid
     *
     * @return Avis
     */
    public function setEtabid($etabid)
    {
        $this->etabid = $etabid;

        return $this;
    }

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
     * Get userid.
     *
     * @return int
     */
    public function getUserid()
    {
        return $this->userid;
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
     * Get rating.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
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

    /**
     * Get dateavis.
     *
     * @return \DateTime
     */
    public function getDateavis()
    {
        return $this->dateavis;
    }
}
