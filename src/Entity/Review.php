<?php


namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Review
 *
 * @ORM\Table(name="review", indexes={@ORM\Index(name="lieuID", columns={"lieuID"}), @ORM\Index(name="userID", columns={"userID"})})
 * @ORM\Entity
 */
class Review
{
    /**
     * @var int
     *
     * @ORM\Column(name="reviewID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $reviewid;

    /**
     * @var int
     *
     * @ORM\Column(name="lieuID", type="integer", nullable=false)
     */
    private $lieuid;

    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userid;

    /**
     * @var float
     *
     * @ORM\Column(name="rating", type="float", precision=10, scale=0, nullable=false)
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=50, nullable=false, options={"default"="aucun commentaire"})
     */
    private $comment = 'aucun commentaire';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="reviewDate", type="date", nullable=true)
     */
    private $reviewdate;


    /**
     * Get reviewid.
     *
     * @return int
     */
    public function getReviewid()
    {
        return $this->reviewid;
    }

    /**
     * Set lieuid.
     *
     * @param int $lieuid
     *
     * @return Review
     */
    public function setLieuid($lieuid)
    {
        $this->lieuid = $lieuid;

        return $this;
    }

    /**
     * Get lieuid.
     *
     * @return int
     */
    public function getLieuid()
    {
        return $this->lieuid;
    }

    /**
     * Set userid.
     *
     * @param int $userid
     *
     * @return Review
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
     * @param float $rating
     *
     * @return Review
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating.
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Review
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set reviewdate.
     *
     * @param \DateTime|null $reviewdate
     *
     * @return Review
     */
    public function setReviewdate($reviewdate = null)
    {
        $this->reviewdate = $reviewdate;

        return $this;
    }

    /**
     * Get reviewdate.
     *
     * @return \DateTime|null
     */
    public function getReviewdate()
    {
        return $this->reviewdate;
    }
}
