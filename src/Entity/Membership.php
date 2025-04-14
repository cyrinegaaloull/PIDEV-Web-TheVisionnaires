<?php

namespace App\Entity;

use App\Entity\Club;
use App\Entity\Users;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity]
#[ORM\Table(name: 'membership')]
class Membership
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'membershipID', type: 'integer', nullable: false)]
    private int $membershipid;

    #[ORM\Column(name: 'membershipStatus', type: 'string', length: 0, nullable: false, options: ['default' => 'EN_ATTENTE'])]
    private string $membershipstatus = 'EN_ATTENTE';

    #[ORM\Column(name: 'requestDate', type: 'date', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTime $requestdate;

    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(name: 'clubID', referencedColumnName: 'clubid', nullable: false, onDelete: 'CASCADE')]
    private Club $clubid;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'memberID', referencedColumnName: 'userId', nullable: false, onDelete: 'CASCADE')]
    private Users $memberid;

    public function getMembershipid(): int
    {
        return $this->membershipid;
    }

    public function getMembershipstatus(): string
    {
        return $this->membershipstatus;
    }

    public function setMembershipstatus(string $membershipstatus): self
    {
        $this->membershipstatus = $membershipstatus;
        return $this;
    }

    public function getRequestdate(): DateTime
    {
        return $this->requestdate;
    }

    public function setRequestdate(DateTime $requestdate): self
    {
        $this->requestdate = $requestdate;
        return $this;
    }

    public function getClubid(): Club
    {
        return $this->clubid;
    }

    public function setClubid(Club $clubid): self
    {
        $this->clubid = $clubid;
        return $this;
    }

    public function getMemberid(): Users
    {
        return $this->memberid;
    }

    public function setMemberid(Users $memberid): self
    {
        $this->memberid = $memberid;
        return $this;
    }
}
