<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "event")]
#[ORM\Index(name: "fk_event_lieu", columns: ["lieuID"])]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "eventID", type: "integer")]
    private ?int $eventid = null;

    #[ORM\Column(name: "eventName", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le nom de l'événement est requis.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le nom doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $eventname = null;

    #[ORM\Column(name: "eventDescription", type: "string", length: 100)]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $eventdescription = null;

    #[ORM\Column(name: "eventDate", type: "date", nullable: true)]
    #[Assert\NotNull(message: "La date est requise.")]
    #[Assert\GreaterThan("today", message: "La date doit être dans le futur.")]
    private ?\DateTimeInterface $eventdate = null;

    #[ORM\Column(name: "eventCategory", type: "string", length: 30)]
    #[Assert\NotBlank(message: "La catégorie est requise.")]
    private ?string $eventcategory = null;

    #[ORM\Column(name: "lieuID", type: "integer")]
    #[Assert\NotNull(message: "Le lieu associé est requis.")]
    private ?int $lieuid = null;

    #[ORM\Column(name: "ticketPrice", type: "integer")]
    #[Assert\NotNull(message: "Le prix du ticket est requis.")]
    #[Assert\PositiveOrZero(message: "Le prix doit être un nombre positif.")]
    private ?int $ticketprice = null;

    #[ORM\Column(name: "eventImage", type: "string", length: 100, nullable: true)]
    private ?string $eventimage = null;

    #[ORM\Column(name: "notificationMethod", type: "string", length: 20, nullable: true)]
    private ?string $notificationmethod = null;

    #[ORM\Column(name: "notificationScheduledAt", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $notificationscheduledat = null;

#[ORM\Column(name: "maxTickets", type: "integer", nullable: false)]
#[Assert\NotNull(message: "Le nombre de tickets est requis.")]
    #[Assert\PositiveOrZero(message: "Le prix doit être un nombre positif.")]
private $maxtickets;
#[ORM\Column(name: "reservedTickets", type: "integer", nullable: false)]
private $reservedtickets = 0;

public function getMaxtickets(): ?int {
    return $this->maxtickets;
}
public function setMaxtickets(?int $maxtickets): self {
    $this->maxtickets = $maxtickets;
    return $this;
}

public function getReservedtickets(): ?int {
    return $this->reservedtickets;
}
public function setReservedtickets(?int $reserved): self {
    $this->reservedtickets = $reserved;
    return $this;
}

public function incrementReservedTickets(): self {
    $this->reservedtickets = ($this->reservedtickets ?? 0) + 1;
    return $this;
}


    public function getEventid(): ?int
    {
        return $this->eventid;
    }

    public function getEventname(): ?string
    {
        return $this->eventname;
    }

    public function setEventname(?string $eventname): self
    {
        $this->eventname = $eventname;
        return $this;
    }

    public function getEventdescription(): ?string
    {
        return $this->eventdescription;
    }

    public function setEventdescription(?string $eventdescription): self
    {
        $this->eventdescription = $eventdescription;
        return $this;
    }

    public function getEventdate(): ?\DateTimeInterface
    {
        return $this->eventdate;
    }

    public function setEventdate(?\DateTimeInterface $eventdate): self
    {
        $this->eventdate = $eventdate;
        return $this;
    }

    public function getEventcategory(): ?string
    {
        return $this->eventcategory;
    }

    public function setEventcategory(?string $eventcategory): self
    {
        $this->eventcategory = $eventcategory;
        return $this;
    }

    public function getLieuid(): ?int
    {
        return $this->lieuid;
    }

    public function setLieuid(?int $lieuid): self
    {
        $this->lieuid = $lieuid;
        return $this;
    }

    public function getTicketprice(): ?int
    {
        return $this->ticketprice;
    }

    public function setTicketprice(?int $ticketprice): self
    {
        $this->ticketprice = $ticketprice;
        return $this;
    }

    public function getEventimage(): ?string
    {
        return $this->eventimage;
    }

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 1eea093 (committing)
    public function setEventimage(?string $eventimage): self
    {
        $this->eventimage = $eventimage;
        return $this;
    }
<<<<<<< HEAD

    public function getNotificationmethod(): ?string
    {
        return $this->notificationmethod;
    }

    public function setNotificationmethod(?string $method): self
    {
        $this->notificationmethod = $method;
        return $this;
    }

    public function getNotificationscheduledat(): ?\DateTimeInterface
    {
        return $this->notificationscheduledat;
    }

    public function setNotificationscheduledat(?\DateTimeInterface $time): self
    {
        $this->notificationscheduledat = $time;
        return $this;
    }
=======
    /**
 * @var string|null
 *
 * @ORM\Column(name="notificationMethod", type="string", length=20, nullable=true)
 */
private $notificationmethod;
=======
>>>>>>> 1eea093 (committing)

    public function getNotificationmethod(): ?string
    {
        return $this->notificationmethod;
    }

<<<<<<< HEAD
>>>>>>> ed8b8e6 (removed secrets from .env)
=======
    public function setNotificationmethod(?string $method): self
    {
        $this->notificationmethod = $method;
        return $this;
    }

    public function getNotificationscheduledat(): ?\DateTimeInterface
    {
        return $this->notificationscheduledat;
    }

    public function setNotificationscheduledat(?\DateTimeInterface $time): self
    {
        $this->notificationscheduledat = $time;
        return $this;
    }
>>>>>>> 1eea093 (committing)
}
