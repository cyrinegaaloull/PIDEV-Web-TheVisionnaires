<?php


namespace App\Entity;
use App\Entity\Club; 
use Doctrine\ORM\Mapping as ORM;

/**
 * Activite
 *
 * @ORM\Table(name="activite", indexes={@ORM\Index(name="clubID", columns={"clubID"})})
 * @ORM\Entity
 */
class Activite
{
    /**
     * @var int
     *
     * @ORM\Column(name="activiteID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $activiteid;

    /**
     * @var string
     *
     * @ORM\Column(name="activiteName", type="string", length=255, nullable=false)
     */
    private $activitename;

    /**
     * @var string
     *
     * @ORM\Column(name="activiteDescription", type="text", length=65535, nullable=false)
     */
    private $activitedescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activiteDate", type="date", nullable=false)
     */
    private $activitedate;

    /**
     * @var string
     *
     * @ORM\Column(name="activiteLocation", type="string", length=255, nullable=false)
     */
    private $activitelocation;

    /**
     * @var string
     *
     * @ORM\Column(name="activiteType", type="string", length=100, nullable=false)
     */
    private $activitetype;

    /**
     * @var string
     *
     * @ORM\Column(name="activiteImage", type="string", length=255, nullable=false)
     */
    private $activiteimage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startTime", type="time", nullable=false)
     */
    private $starttime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endTime", type="time", nullable=false)
     */
    private $endtime;

    /**
     * @var string
     *
     * @ORM\Column(name="activiteStatus", type="string", length=20, nullable=false, options={"default"="A_venir"})
     */
    private $activitestatus = 'A_venir';

    /**
     * @var Club
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Club", inversedBy="activities")
     * @ORM\JoinColumn(name="clubID", referencedColumnName="clubID", onDelete="CASCADE")
     */
    private $clubid;



    /**
     * Get activiteid.
     *
     * @return int
     */
    public function getActiviteid()
    {
        return $this->activiteid;
    }

    /**
     * Set activitename.
     *
     * @param string $activitename
     *
     * @return Activite
     */
    public function setActivitename($activitename)
    {
        $this->activitename = $activitename;

        return $this;
    }

    /**
     * Get activitename.
     *
     * @return string
     */
    public function getActivitename()
    {
        return $this->activitename;
    }

    /**
     * Set activitedescription.
     *
     * @param string $activitedescription
     *
     * @return Activite
     */
    public function setActivitedescription($activitedescription)
    {
        $this->activitedescription = $activitedescription;

        return $this;
    }

    /**
     * Get activitedescription.
     *
     * @return string
     */
    public function getActivitedescription()
    {
        return $this->activitedescription;
    }

    /**
     * Set activitedate.
     *
     * @param \DateTime $activitedate
     *
     * @return Activite
     */
    public function setActivitedate($activitedate)
    {
        $this->activitedate = $activitedate;

        return $this;
    }

    /**
     * Get activitedate.
     *
     * @return \DateTime
     */
    public function getActivitedate()
    {
        return $this->activitedate;
    }

    /**
     * Set activitelocation.
     *
     * @param string $activitelocation
     *
     * @return Activite
     */
    public function setActivitelocation($activitelocation)
    {
        $this->activitelocation = $activitelocation;

        return $this;
    }

    /**
     * Get activitelocation.
     *
     * @return string
     */
    public function getActivitelocation()
    {
        return $this->activitelocation;
    }

    /**
     * Set activitetype.
     *
     * @param string $activitetype
     *
     * @return Activite
     */
    public function setActivitetype($activitetype)
    {
        $this->activitetype = $activitetype;

        return $this;
    }

    /**
     * Get activitetype.
     *
     * @return string
     */
    public function getActivitetype()
    {
        return $this->activitetype;
    }

    /**
     * Set activiteimage.
     *
     * @param string $activiteimage
     *
     * @return Activite
     */
    public function setActiviteimage($activiteimage)
    {
        $this->activiteimage = $activiteimage;

        return $this;
    }

    /**
     * Get activiteimage.
     *
     * @return string
     */
    public function getActiviteimage()
    {
        return $this->activiteimage;
    }

    /**
     * Set starttime.
     *
     * @param \DateTime $starttime
     *
     * @return Activite
     */
    public function setStarttime($starttime)
    {
        $this->starttime = $starttime;

        return $this;
    }

    /**
     * Get starttime.
     *
     * @return \DateTime
     */
    public function getStarttime()
    {
        return $this->starttime;
    }

    /**
     * Set endtime.
     *
     * @param \DateTime $endtime
     *
     * @return Activite
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;

        return $this;
    }

    /**
     * Get endtime.
     *
     * @return \DateTime
     */
    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
     * Set activitestatus.
     *
     * @param string $activitestatus
     *
     * @return Activite
     */
    public function setActivitestatus($activitestatus)
    {
        $this->activitestatus = $activitestatus;

        return $this;
    }

    /**
     * Get activitestatus.
     *
     * @return string
     */
    public function getActivitestatus()
    {
        return $this->activitestatus;
    }

    /**
     * Set clubid.
     *
     * @param Club|null $clubid
     *
     * @return Activite
     */
    public function setClubid(Club $clubid = null)
    {
        $this->clubid = $clubid;

        return $this;
    }

    /**
     * Get clubid.
     *
     * @return Club|null
     */
    public function getClubid()
    {
        return $this->clubid;
    }
}
