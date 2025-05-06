<?php

namespace App\Entity;

use App\Entity\Roles;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'role_id', columns: ['role_id'])]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'user_id', type: 'integer', nullable: false)]
    private ?int $userId = null;

    #[ORM\Column(name: 'username', type: 'string', length: 255, nullable: false)]
    private string $username;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false)]
    private string $email;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private string $password;

    #[ORM\Column(name: 'nom', type: 'string', length: 255, nullable: false)]
    private string $nom;

    #[ORM\Column(name: 'prenom', type: 'string', length: 255, nullable: false)]
    private string $prenom;

    #[ORM\Column(name: 'profile_picture', type: 'string', length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\ManyToOne(targetEntity: Roles::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    private ?Roles $role = null;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setRole(?Roles $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    public function getRoles(): array
    {
        $userRoles = [];
        if ($this->role) {
            $userRoles[] = $this->role->getName();
        }
        $userRoles[] = 'ROLE_USER';
        return array_unique($userRoles);
    }

    public function eraseCredentials(): void
    {
        // Clear temporary, sensitive data if stored
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getSalt(): ?string
    {
        return null;
    }
}
