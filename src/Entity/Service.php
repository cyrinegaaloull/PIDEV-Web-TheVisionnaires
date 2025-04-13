<?php

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

    /**
     * Set servicename.
     *
     * @param string $servicename
     *
     * @return Service
     */
    public function setServicename($servicename)
    {
        $this->servicename = $servicename;

        return $this;
    }

    /**
     * Get servicename.
     *
     * @return string
     */
    public function getServicename()
    {
        return $this->servicename;
    }

    /**
     * Set serviceprix.
     *
     * @param float $serviceprix
     *
     * @return Service
     */
    public function setServiceprix($serviceprix)
    {
        $this->serviceprix = $serviceprix;

        return $this;
    }

    /**
     * Get serviceprix.
     *
     * @return float
     */
    public function getServiceprix()
    {
        return $this->serviceprix;
    }

    /**
     * Set etabid.
     *
     * @param int $etabid
     *
     * @return Service
     */
    public function setEtabid($etabid)
    {
        $this->etabid = $etabid;

        return $this;
    }

    /**
     * Get etabid.
     *
     * @return int
     */
    public function getEtabid()
    {
        return $this->etabid;
    }
}