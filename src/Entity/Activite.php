<?php

namespace App\Entity;

use App\Entity\Club;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activite')]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'activiteID', type: 'integer')]
    private int $activiteid;

    #[ORM\Column(name: 'activiteName', type: 'string', length: 255)]
    private string $activitename;

    #[ORM\Column(name: 'activiteDescription', type: 'text', length: 65535)]
    private string $activitedescription;

    #[ORM\Column(name: 'activiteDate', type: 'date')]
    private \DateTimeInterface $activitedate;

    #[ORM\Column(name: 'activiteLocation', type: 'string', length: 255)]
    private string $activitelocation;

    #[ORM\Column(name: 'activiteType', type: 'string', length: 100)]
    private string $activitetype;

    #[ORM\Column(name: 'activiteImage', type: 'string', length: 255)]
    private string $activiteimage;

    #[ORM\Column(name: 'startTime', type: 'time')]
    private \DateTimeInterface $starttime;

    #[ORM\Column(name: 'endTime', type: 'time')]
    private \DateTimeInterface $endtime;

    #[ORM\Column(name: 'activiteStatus', type: 'string', length: 20, options: ['default' => 'A_venir'])]
    private string $activitestatus = 'A_venir';

    #[ORM\ManyToOne(targetEntity: Club::class, inversedBy: 'activities')]
    #[ORM\JoinColumn(name: 'clubID', referencedColumnName: 'clubID', onDelete: 'CASCADE')]
    private ?Club $clubid = null;

    // Getters & Setters

    public function getActiviteid(): int
    {
        return $this->activiteid;
    }

    public function setActivitename(string $activitename): self
    {
        $this->activitename = $activitename;
        return $this;
    }

    public function getActivitename(): string
    {
        return $this->activitename;
    }

    public function setActivitedescription(string $activitedescription): self
    {
        $this->activitedescription = $activitedescription;
        return $this;
    }

    public function getActivitedescription(): string
    {
        return $this->activitedescription;
    }

    public function setActivitedate(\DateTimeInterface $activitedate): self
    {
        $this->activitedate = $activitedate;
        return $this;
    }

    public function getActivitedate(): \DateTimeInterface
    {
        return $this->activitedate;
    }

    public function setActivitelocation(string $activitelocation): self
    {
        $this->activitelocation = $activitelocation;
        return $this;
    }

    public function getActivitelocation(): string
    {
        return $this->activitelocation;
    }

    public function setActivitetype(string $activitetype): self
    {
        $this->activitetype = $activitetype;
        return $this;
    }

    public function getActivitetype(): string
    {
        return $this->activitetype;
    }

    public function setActiviteimage(string $activiteimage): self
    {
        $this->activiteimage = $activiteimage;
        return $this;
    }

    public function getActiviteimage(): string
    {
        return $this->activiteimage;
    }

    public function setStarttime(\DateTimeInterface $starttime): self
    {
        $this->starttime = $starttime;
        return $this;
    }

    public function getStarttime(): \DateTimeInterface
    {
        return $this->starttime;
    }

    public function setEndtime(\DateTimeInterface $endtime): self
    {
        $this->endtime = $endtime;
        return $this;
    }

    public function getEndtime(): \DateTimeInterface
    {
        return $this->endtime;
    }

    public function setActivitestatus(string $activitestatus): self
    {
        $this->activitestatus = $activitestatus;
        return $this;
    }

    public function getActivitestatus(): string
    {
        return $this->activitestatus;
    }

    public function setClubid(?Club $clubid): self
    {
        $this->clubid = $clubid;
        return $this;
    }

    public function getClubid(): ?Club
    {
        return $this->clubid;
    }
}
