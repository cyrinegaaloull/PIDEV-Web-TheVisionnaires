<?php

// src/Entity/Service.php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'service')]
#[ORM\Index(name: 'etabID', columns: ['etabID'])]
#[ORM\Entity]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'serviceID', type: 'integer', nullable: false)]
    private $serviceid;

    #[ORM\Column(name: 'serviceName', type: 'string', length: 50, nullable: false)]
    private $servicename;

    #[ORM\Column(name: 'servicePrix', type: 'float', precision: 10, scale: 0, nullable: false)]
    private $serviceprix;

    #[ORM\ManyToOne(targetEntity: Etablissement::class)]
    #[ORM\JoinColumn(name: 'etabID', referencedColumnName: 'etabID')]
    private $etablissement;

    public function getServiceid(): ?int

    #[ORM\Column(name: 'etabID', type: 'integer', nullable: false)]
    private $etabid;

    /**
     * Get serviceid.
     *
     * @return int
     */
    public function getServiceid()
    {
        return $this->serviceid;
    }

    public function setServicename(string $servicename): self
    {
        $this->servicename = $servicename;

        return $this;
    }

    public function getServicename(): ?string
    {
        return $this->servicename;
    }

    public function setServiceprix(float $serviceprix): self
    {
        $this->serviceprix = $serviceprix;

        return $this;
    }

    public function getServiceprix(): ?float
    {
        return $this->serviceprix;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function getEtabid(): ?int
    {
        return $this->etablissement ? $this->etablissement->getEtabid() : null;
    }
}