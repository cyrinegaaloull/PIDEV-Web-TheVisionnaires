<?php

namespace App\Entity;

use App\Entity\Club;
use App\Entity\Users;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use App\Repository\ClubMembersRepository;

#[ORM\Entity(repositoryClass: ClubMembersRepository::class)]
#[ORM\Table(name: 'club_members')]
class ClubMembers
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(name: 'clubID', referencedColumnName: 'clubid', nullable: false, onDelete: 'CASCADE')]
    private Club $clubid;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'userID', referencedColumnName: 'userId', nullable: false, onDelete: 'CASCADE')]
    private Users $userid;

    #[ORM\Column(name: 'joinDate', type: 'date', nullable: true, options: ['default' => 'CURRENT_DATE'])]
    private ?DateTime $joindate = null;

    public function getClubid(): Club
    {
        return $this->clubid;
    }

    public function setClubid(Club $clubid): self
    {
        $this->clubid = $clubid;
        return $this;
    }

    public function getUserid(): Users
    {
        return $this->userid;
    }

    public function setUserid(Users $userid): self
    {
        $this->userid = $userid;
        return $this;
    }

    public function getJoindate(): ?DateTime
    {
        return $this->joindate;
    }

    public function setJoindate(?DateTime $joindate): self
    {
        $this->joindate = $joindate;
        return $this;
    }
}
