<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Roles")]
class Roles
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private int $id;

    #[ORM\Column(name: "role", type: "string", length: 50, nullable: false)]
    private string $role;

    public function getId(): int
    {
        return $this->id;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Alias pour Symfony Security : retourne un rôle valide (ex: ROLE_ADMIN)
     */
    public function getName(): string
    {
        $roleName = $this->role;
        if (!str_starts_with($roleName, 'ROLE_')) {
            $roleName = 'ROLE_' . strtoupper($roleName);
        }
        return $roleName;
    }
}