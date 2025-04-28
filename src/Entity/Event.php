<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EventRepository;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: "event")]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "eventID", type: "integer")]
    private ?int $eventid = null;

    #[ORM\Column(name: "eventName", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le nom de l'événement est requis.")]
    #[Assert\Length(min: 3, max: 100)]
    private ?string $eventname = null;

    #[ORM\Column(name: "eventDescription", type: "string", length: 255)]
    #[Assert\NotBlank(message: "La description est requise.")]
    #[Assert\Length(min: 10, max: 255)]
    private ?string $eventdescription = null;

    #[ORM\Column(name: "eventDate", type: "date", nullable: true)]
    #[Assert\NotNull(message: "La date est requise.")]
    #[Assert\GreaterThanOrEqual("today", message: "La date doit être aujourd'hui ou dans le futur.")]
    private ?\DateTimeInterface $eventdate = null;

    #[ORM\Column(name: "eventCategory", type: "string", length: 30)]
    #[Assert\NotBlank(message: "La catégorie est requise.")]
    private ?string $eventcategory = null;

    #[ORM\ManyToOne(targetEntity: Lieu::class)]
    #[ORM\JoinColumn(name: "lieuID", referencedColumnName: "lieuID", nullable: false)]
    #[Assert\NotNull(message: "Le lieu associé est requis.")]
    private ?Lieu $lieu = null;

    #[ORM\Column(name: "ticketPrice", type: "integer")]
    #[Assert\NotNull(message: "Le prix du ticket est requis.")]
    #[Assert\PositiveOrZero(message: "Le prix doit être un nombre positif.")]
    private ?int $ticketprice = null;

    #[ORM\Column(name: "eventImage", type: "string", length: 100, nullable: true)]
    private ?string $eventimage = null;


    #[ORM\Column(name: "maxTickets", type: "integer")]
    #[Assert\NotNull(message: "Le nombre de tickets est requis.")]
    #[Assert\Positive(message: "Le nombre de tickets doit être supérieur à 0.")]
    private ?int $maxtickets = null;

    #[ORM\Column(name: "reservedTickets", type: "integer")]
    private ?int $reservedtickets = 0;

    // 🔻 Getters & Setters

    public function getEventid(): ?int { return $this->eventid; }

    public function getEventname(): ?string { return $this->eventname; }
    public function setEventname(?string $eventname): self { $this->eventname = $eventname; return $this; }

    public function getEventdescription(): ?string { return $this->eventdescription; }
    public function setEventdescription(?string $eventdescription): self { $this->eventdescription = $eventdescription; return $this; }

    public function getEventdate(): ?\DateTimeInterface { return $this->eventdate; }
    public function setEventdate(?\DateTimeInterface $eventdate): self { $this->eventdate = $eventdate; return $this; }

    public function getEventcategory(): ?string { return $this->eventcategory; }
    public function setEventcategory(?string $eventcategory): self { $this->eventcategory = $eventcategory; return $this; }

    public function getLieu(): ?Lieu { return $this->lieu; }
    public function setLieu(?Lieu $lieu): self { $this->lieu = $lieu; return $this; }

    public function getTicketprice(): ?int { return $this->ticketprice; }
    public function setTicketprice(?int $ticketprice): self { $this->ticketprice = $ticketprice; return $this; }

    public function getEventimage(): ?string { return $this->eventimage; }
    public function setEventimage(?string $eventimage): self { $this->eventimage = $eventimage; return $this; }

    public function getMaxtickets(): ?int { return $this->maxtickets; }
    public function setMaxtickets(?int $maxtickets): self { $this->maxtickets = $maxtickets; return $this; }

    public function getReservedtickets(): ?int { return $this->reservedtickets; }
    public function setReservedtickets(?int $reserved): self { $this->reservedtickets = $reserved; return $this; }
    public function incrementReservedTickets(): self
    {
        $this->reservedtickets = ($this->reservedtickets ?? 0) + 1;
        return $this;
    }
    
}
