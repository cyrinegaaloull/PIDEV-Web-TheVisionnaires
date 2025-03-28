<?php


namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Membership
 *
 * @ORM\Table(name="membership", indexes={@ORM\Index(name="idx_clubID", columns={"clubID"}), @ORM\Index(name="fk_applicantID", columns={"memberID"})})
 * @ORM\Entity
 */
class Membership
{
    /**
     * @var int
     *
     * @ORM\Column(name="membershipID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $membershipid;

    /**
     * @var string
     *
     * @ORM\Column(name="membershipStatus", type="string", length=0, nullable=false, options={"default"="EN_ATTENTE"})
     */
    private $membershipstatus = 'EN_ATTENTE';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="requestDate", type="date", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $requestdate = 'CURRENT_TIMESTAMP';

    /**
     * @var int
     *
     * @ORM\Column(name="clubID", type="integer", nullable=false)
     */
    private $clubid;

    /**
     * @var int
     *
     * @ORM\Column(name="memberID", type="integer", nullable=false)
     */
    private $memberid;


    /**
     * Get membershipid.
     *
     * @return int
     */
    public function getMembershipid()
    {
        return $this->membershipid;
    }

    /**
     * Set membershipstatus.
     *
     * @param string $membershipstatus
     *
     * @return Membership
     */
    public function setMembershipstatus($membershipstatus)
    {
        $this->membershipstatus = $membershipstatus;

        return $this;
    }

    /**
     * Get membershipstatus.
     *
     * @return string
     */
    public function getMembershipstatus()
    {
        return $this->membershipstatus;
    }

    /**
     * Set requestdate.
     *
     * @param \DateTime $requestdate
     *
     * @return Membership
     */
    public function setRequestdate($requestdate)
    {
        $this->requestdate = $requestdate;

        return $this;
    }

    /**
     * Get requestdate.
     *
     * @return \DateTime
     */
    public function getRequestdate()
    {
        return $this->requestdate;
    }

    /**
     * Set clubid.
     *
     * @param int $clubid
     *
     * @return Membership
     */
    public function setClubid($clubid)
    {
        $this->clubid = $clubid;

        return $this;
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
     * Set memberid.
     *
     * @param int $memberid
     *
     * @return Membership
     */
    public function setMemberid($memberid)
    {
        $this->memberid = $memberid;

        return $this;
    }

    /**
     * Get memberid.
     *
     * @return int
     */
    public function getMemberid()
    {
        return $this->memberid;
    }
}
