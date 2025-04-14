<?php

namespace App\Entity;

use App\Entity\Activite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: "club")]
#[ORM\UniqueConstraint(name: "unique_club_name", columns: ["clubName"])]
#[UniqueEntity(fields: ["clubname"], message: "Ce nom de club existe déjà. Veuillez en choisir un autre.")]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "clubID", type: "integer", nullable: false)]
    private $clubid;

    #[ORM\Column(name: "clubName", type: "string", length: 50, nullable: false)]
    private $clubname;

    #[ORM\Column(name: "clubDescription", type: "string", length: 255, nullable: false)]
    private $clubdescription;

    #[ORM\Column(name: "clubCategory", type: "string", length: 0, nullable: true)]
    private $clubcategory;

    #[ORM\Column(name: "clubLogo", type: "string", length: 255, nullable: false)]
    private $clublogo;

    #[ORM\Column(name: "clubContact", type: "string", length: 255, nullable: false)]
    private $clubcontact;

    #[ORM\Column(name: "clubLocation", type: "string", length: 255, nullable: false)]
    private $clublocation;

    #[ORM\Column(name: "creationDate", type: "date", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    private $creationdate;

    #[ORM\Column(name: "membersCount", type: "integer", nullable: false)]
    private $memberscount;

    #[ORM\Column(name: "scheduleInfo", type: "text", length: 65535, nullable: false)]
    private $scheduleinfo;

    #[ORM\Column(name: "bannerImage", type: "string", length: 255, nullable: false)]
    private $bannerimage;

    #[ORM\OneToMany(targetEntity: Activite::class, mappedBy: "clubid", orphanRemoval: true)]
    private $activities;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
    }

    /**
     * Get clubid.
     *
     * @return int
     */
    public function getClubid()
    {
        return $this->clubid;
    }

    /**
     * Set clubname.
     *
     * @param string $clubname
     *
     * @return Club
     */
    public function setClubname($clubname)
    {
        $this->clubname = $clubname;

        return $this;
    }

    /**
     * Get clubname.
     *
     * @return string
     */
    public function getClubname()
    {
        return $this->clubname;
    }

    /**
     * Set clubdescription.
     *
     * @param string $clubdescription
     *
     * @return Club
     */
    public function setClubdescription($clubdescription)
    {
        $this->clubdescription = $clubdescription;

        return $this;
    }

    /**
     * Get clubdescription.
     *
     * @return string
     */
    public function getClubdescription()
    {
        return $this->clubdescription;
    }

    /**
     * Set clubcategory.
     *
     * @param string|null $clubcategory
     *
     * @return Club
     */
    public function setClubcategory($clubcategory = null)
    {
        $this->clubcategory = $clubcategory;

        return $this;
    }

    /**
     * Get clubcategory.
     *
     * @return string|null
     */
    public function getClubcategory()
    {
        return $this->clubcategory;
    }

    /**
     * Set clublogo.
     *
     * @param string $clublogo
     *
     * @return Club
     */
    public function setClublogo($clublogo)
    {
        $this->clublogo = $clublogo;

        return $this;
    }

    /**
     * Get clublogo.
     *
     * @return string
     */
    public function getClublogo()
    {
        return $this->clublogo;
    }

    /**
     * Set clubcontact.
     *
     * @param string $clubcontact
     *
     * @return Club
     */
    public function setClubcontact($clubcontact)
    {
        $this->clubcontact = $clubcontact;

        return $this;
    }

    /**
     * Get clubcontact.
     *
     * @return string
     */
    public function getClubcontact()
    {
        return $this->clubcontact;
    }

    /**
     * Set clublocation.
     *
     * @param string $clublocation
     *
     * @return Club
     */
    public function setClublocation($clublocation)
    {
        $this->clublocation = $clublocation;

        return $this;
    }

    /**
     * Get clublocation.
     *
     * @return string
     */
    public function getClublocation()
    {
        return $this->clublocation;
    }

    /**
     * Set creationdate.
     *
     * @param \DateTime $creationdate
     *
     * @return Club
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate;

        return $this;
    }

    /**
     * Get creationdate.
     *
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Set memberscount.
     *
     * @param int $memberscount
     *
     * @return Club
     */
    public function setMemberscount($memberscount)
    {
        $this->memberscount = $memberscount;

        return $this;
    }

    /**
     * Get memberscount.
     *
     * @return int
     */
    public function getMemberscount()
    {
        return $this->memberscount;
    }

    /**
     * Set scheduleinfo.
     *
     * @param string $scheduleinfo
     *
     * @return Club
     */
    public function setScheduleinfo($scheduleinfo)
    {
        $this->scheduleinfo = $scheduleinfo;

        return $this;
    }

    /**
     * Get scheduleinfo.
     *
     * @return string
     */
    public function getScheduleinfo()
    {
        return $this->scheduleinfo;
    }

    /**
     * Set bannerimage.
     *
     * @param string $bannerimage
     *
     * @return Club
     */
    public function setBannerimage($bannerimage)
    {
        $this->bannerimage = $bannerimage;

        return $this;
    }

    /**
     * Get bannerimage.
     *
     * @return string
     */
    public function getBannerimage()
    {
        return $this->bannerimage;
    }


    /**
    * @return Collection<int, Activite>
    */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activite $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setClubid($this);
        }

    return $this;
    }

    public function removeActivity(Activite $activity): self
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getClubid() === $this) {
                $activity->setClubid(null);
            }
        }

        return $this;
    }


}
