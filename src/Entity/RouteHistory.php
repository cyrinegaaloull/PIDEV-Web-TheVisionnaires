<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "route_history")]
#[ORM\Entity]
class RouteHistory
{
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;

    #[ORM\Column(name: "name", type: "text", length: 65535, nullable: false)]
    private $name;

    #[ORM\Column(name: "departure_place_name", type: "text", length: 65535, nullable: true)]
    private $departurePlaceName;

    #[ORM\Column(name: "departure_lat", type: "float", precision: 10, scale: 0, nullable: false)]
    private $departureLat;

    #[ORM\Column(name: "departure_lon", type: "float", precision: 10, scale: 0, nullable: false)]
    private $departureLon;

    #[ORM\Column(name: "arrival_place_name", type: "text", length: 65535, nullable: true)]
    private $arrivalPlaceName;

    #[ORM\Column(name: "arrival_lat", type: "float", precision: 10, scale: 0, nullable: false)]
    private $arrivalLat;

    #[ORM\Column(name: "arrival_lon", type: "float", precision: 10, scale: 0, nullable: false)]
    private $arrivalLon;

    #[ORM\Column(name: "transport_mode", type: "text", length: 65535, nullable: false)]
    private $transportMode;

    #[ORM\Column(name: "timestamp", type: "text", length: 65535, nullable: false)]
    private $timestamp;

    #[ORM\Column(name: "description", type: "text", length: 65535, nullable: true)]
    private $description;

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
     * Set name.
     *
     * @param string $name
     *
     * @return RouteHistory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set departurePlaceName.
     *
     * @param string|null $departurePlaceName
     *
     * @return RouteHistory
     */
    public function setDeparturePlaceName($departurePlaceName = null)
    {
        $this->departurePlaceName = $departurePlaceName;

        return $this;
    }

    /**
     * Get departurePlaceName.
     *
     * @return string|null
     */
    public function getDeparturePlaceName()
    {
        return $this->departurePlaceName;
    }

    /**
     * Set departureLat.
     *
     * @param float $departureLat
     *
     * @return RouteHistory
     */
    public function setDepartureLat($departureLat)
    {
        $this->departureLat = $departureLat;

        return $this;
    }

    /**
     * Get departureLat.
     *
     * @return float
     */
    public function getDepartureLat()
    {
        return $this->departureLat;
    }

    /**
     * Set departureLon.
     *
     * @param float $departureLon
     *
     * @return RouteHistory
     */
    public function setDepartureLon($departureLon)
    {
        $this->departureLon = $departureLon;

        return $this;
    }

    /**
     * Get departureLon.
     *
     * @return float
     */
    public function getDepartureLon()
    {
        return $this->departureLon;
    }

    /**
     * Set arrivalPlaceName.
     *
     * @param string|null $arrivalPlaceName
     *
     * @return RouteHistory
     */
    public function setArrivalPlaceName($arrivalPlaceName = null)
    {
        $this->arrivalPlaceName = $arrivalPlaceName;

        return $this;
    }

    /**
     * Get arrivalPlaceName.
     *
     * @return string|null
     */
    public function getArrivalPlaceName()
    {
        return $this->arrivalPlaceName;
    }

    /**
     * Set arrivalLat.
     *
     * @param float $arrivalLat
     *
     * @return RouteHistory
     */
    public function setArrivalLat($arrivalLat)
    {
        $this->arrivalLat = $arrivalLat;

        return $this;
    }

    /**
     * Get arrivalLat.
     *
     * @return float
     */
    public function getArrivalLat()
    {
        return $this->arrivalLat;
    }

    /**
     * Set arrivalLon.
     *
     * @param float $arrivalLon
     *
     * @return RouteHistory
     */
    public function setArrivalLon($arrivalLon)
    {
        $this->arrivalLon = $arrivalLon;

        return $this;
    }

    /**
     * Get arrivalLon.
     *
     * @return float
     */
    public function getArrivalLon()
    {
        return $this->arrivalLon;
    }

    /**
     * Set transportMode.
     *
     * @param string $transportMode
     *
     * @return RouteHistory
     */
    public function setTransportMode($transportMode)
    {
        $this->transportMode = $transportMode;

        return $this;
    }

    /**
     * Get transportMode.
     *
     * @return string
     */
    public function getTransportMode()
    {
        return $this->transportMode;
    }

    /**
     * Set timestamp.
     *
     * @param string $timestamp
     *
     * @return RouteHistory
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return RouteHistory
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
