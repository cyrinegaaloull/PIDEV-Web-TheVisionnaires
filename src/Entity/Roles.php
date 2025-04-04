<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Roles
 *
 * @ORM\Table(name="Roles")
 * @ORM\Entity
 */
class Roles
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=50, nullable=false)
     */
    private $role;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role.
     *
     * @param string $role
     *
     * @return Roles
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * Get name - Alias for getRole() to work with Symfony security.
     *
     * @return string
     */
    public function getName()
    {
        // Make sure the role string starts with ROLE_
        $roleName = $this->role;
        if (!str_starts_with($roleName, 'ROLE_')) {
            $roleName = 'ROLE_' . strtoupper($roleName);
        }
        return $roleName;
    }
}