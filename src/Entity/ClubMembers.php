<?php


namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClubMembers
 *
 * @ORM\Table(name="club_members", indexes={@ORM\Index(name="userID", columns={"userID"})})
 * @ORM\Entity
 */
class ClubMembers
{
    /**
     * @var int
     *
     * @ORM\Column(name="clubID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $clubid;

    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $userid;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="joinDate", type="date", nullable=true, options={"default"="CURRENT_DATE"})
     */
    private $joindate = 'CURRENT_DATE';


    /**
     * Set clubid.
     *
     * @param int $clubid
     *
     * @return ClubMembers
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
     * Set userid.
     *
     * @param int $userid
     *
     * @return ClubMembers
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
     * Set joindate.
     *
     * @param \DateTime|null $joindate
     *
     * @return ClubMembers
     */
    public function setJoindate($joindate = null)
    {
        $this->joindate = $joindate;

        return $this;
    }

    /**
     * Get joindate.
     *
     * @return \DateTime|null
     */
    public function getJoindate()
    {
        return $this->joindate;
    }
}
