<?php
// src/Entity/ActivityReminder.php

namespace App\Entity;

use App\Entity\Users;
use App\Entity\Activite;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'activity_reminders')]
class ActivityReminder
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE')]
    private Users $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Activite::class)]
    #[ORM\JoinColumn(name: 'activite_id', referencedColumnName: 'activiteID', onDelete: 'CASCADE')]
    private Activite $activite;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $sentAt;

    public function getUser(): Users { return $this->user; }
    public function setUser(Users $user): self { $this->user = $user; return $this; }

    public function getActivite(): Activite { return $this->activite; }
    public function setActivite(Activite $activite): self { $this->activite = $activite; return $this; }

    public function getSentAt(): \DateTimeInterface { return $this->sentAt; }
    public function setSentAt(\DateTimeInterface $sentAt): self { $this->sentAt = $sentAt; return $this; }
}
