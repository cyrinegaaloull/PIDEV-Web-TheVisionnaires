<?php


namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event", indexes={@ORM\Index(name="fk_event_lieu", columns={"lieuID"})})
 * @ORM\Entity
 */
class Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="eventID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $eventid;

    /**
     * @var string
     *
     * @ORM\Column(name="eventName", type="string", length=100, nullable=false)
     */
    private $eventname;

    /**
     * @var string
     *
     * @ORM\Column(name="eventDescription", type="string", length=100, nullable=false)
     */
    private $eventdescription;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="eventDate", type="date", nullable=true)
     */
    private $eventdate;

    /**
     * @var string
     *
     * @ORM\Column(name="eventCategory", type="string", length=30, nullable=false)
     */
    private $eventcategory;

    /**
     * @var int
     *
     * @ORM\Column(name="lieuID", type="integer", nullable=false)
     */
    private $lieuid;

    /**
     * @var int
     *
     * @ORM\Column(name="ticketPrice", type="integer", nullable=false)
     */
    private $ticketprice;

    /**
     * @var string|null
     *
     * @ORM\Column(name="eventImage", type="string", length=100, nullable=true)
     */
    private $eventimage;


    /**
     * Get eventid.
     *
     * @return int
     */
    public function getEventid()
    {
        return $this->eventid;
    }

    /**
     * Set eventname.
     *
     * @param string $eventname
     *
     * @return Event
     */
    public function setEventname($eventname)
    {
        $this->eventname = $eventname;

        return $this;
    }

    /**
     * Get eventname.
     *
     * @return string
     */
    public function getEventname()
    {
        return $this->eventname;
    }

    /**
     * Set eventdescription.
     *
     * @param string $eventdescription
     *
     * @return Event
     */
    public function setEventdescription($eventdescription)
    {
        $this->eventdescription = $eventdescription;

        return $this;
    }

    /**
     * Get eventdescription.
     *
     * @return string
     */
    public function getEventdescription()
    {
        return $this->eventdescription;
    }

    /**
     * Set eventdate.
     *
     * @param \DateTime|null $eventdate
     *
     * @return Event
     */
    public function setEventdate($eventdate = null)
    {
        $this->eventdate = $eventdate;

        return $this;
    }

    /**
     * Get eventdate.
     *
     * @return \DateTime|null
     */
    public function getEventdate()
    {
        return $this->eventdate;
    }

    /**
     * Set eventcategory.
     *
     * @param string $eventcategory
     *
     * @return Event
     */
    public function setEventcategory($eventcategory)
    {
        $this->eventcategory = $eventcategory;

        return $this;
    }

    /**
     * Get eventcategory.
     *
     * @return string
     */
    public function getEventcategory()
    {
        return $this->eventcategory;
    }

    /**
     * Set lieuid.
     *
     * @param int $lieuid
     *
     * @return Event
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
     * Set ticketprice.
     *
     * @param int $ticketprice
     *
     * @return Event
     */
    public function setTicketprice($ticketprice)
    {
        $this->ticketprice = $ticketprice;

        return $this;
    }

    /**
     * Get ticketprice.
     *
     * @return int
     */
    public function getTicketprice()
    {
        return $this->ticketprice;
    }

    /**
     * Set eventimage.
     *
     * @param string|null $eventimage
     *
     * @return Event
     */
    public function setEventimage($eventimage = null)
    {
        $this->eventimage = $eventimage;

        return $this;
    }

    /**
     * Get eventimage.
     *
     * @return string|null
     */
    public function getEventimage()
    {
        return $this->eventimage;
    }
}
