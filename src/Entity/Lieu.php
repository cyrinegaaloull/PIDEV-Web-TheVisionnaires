<?php


namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lieu
 *
 * @ORM\Table(name="lieu")
 * @ORM\Entity
 */
class Lieu
{
    /**
     * @var int
     *
     * @ORM\Column(name="lieuID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $lieuid;

    /**
     * @var string
     *
     * @ORM\Column(name="lieuName", type="string", length=100, nullable=false)
     */
    private $lieuname;

    /**
     * @var string
     *
     * @ORM\Column(name="lieuAddress", type="string", length=100, nullable=false)
     */
    private $lieuaddress;

    /**
     * @var string
     *
     * @ORM\Column(name="lieuDescription", type="string", length=100, nullable=false)
     */
    private $lieudescription;

    /**
     * @var string
     *
     * @ORM\Column(name="lieuCategory", type="string", length=30, nullable=false)
     */
    private $lieucategory;

    /**
     * @var string
     *
     * @ORM\Column(name="lieuOpeningHours", type="text", length=65535, nullable=false)
     */
    private $lieuopeninghours;

    /**
     * @var string
     *
     * @ORM\Column(name="lieuClosingHours", type="text", length=65535, nullable=false)
     */
    private $lieuclosinghours;

    /**
     * @var int|null
     *
     * @ORM\Column(name="lieuNumber", type="integer", nullable=true)
     */
    private $lieunumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lieuImage", type="string", length=20, nullable=true)
     */
    private $lieuimage;

    /**
     * @var float|null
     *
     * @ORM\Column(name="latitude", type="float", precision=10, scale=0, nullable=true)
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(name="longitude", type="float", precision=10, scale=0, nullable=true)
     */
    private $longitude;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="isFavorite", type="boolean", nullable=true)
     */
    private $isfavorite = '0';


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
     * Set lieuname.
     *
     * @param string $lieuname
     *
     * @return Lieu
     */
    public function setLieuname($lieuname)
    {
        $this->lieuname = $lieuname;

        return $this;
    }

    /**
     * Get lieuname.
     *
     * @return string
     */
    public function getLieuname()
    {
        return $this->lieuname;
    }

    /**
     * Set lieuaddress.
     *
     * @param string $lieuaddress
     *
     * @return Lieu
     */
    public function setLieuaddress($lieuaddress)
    {
        $this->lieuaddress = $lieuaddress;

        return $this;
    }

    /**
     * Get lieuaddress.
     *
     * @return string
     */
    public function getLieuaddress()
    {
        return $this->lieuaddress;
    }

    /**
     * Set lieudescription.
     *
     * @param string $lieudescription
     *
     * @return Lieu
     */
    public function setLieudescription($lieudescription)
    {
        $this->lieudescription = $lieudescription;

        return $this;
    }

    /**
     * Get lieudescription.
     *
     * @return string
     */
    public function getLieudescription()
    {
        return $this->lieudescription;
    }

    /**
     * Set lieucategory.
     *
     * @param string $lieucategory
     *
     * @return Lieu
     */
    public function setLieucategory($lieucategory)
    {
        $this->lieucategory = $lieucategory;

        return $this;
    }

    /**
     * Get lieucategory.
     *
     * @return string
     */
    public function getLieucategory()
    {
        return $this->lieucategory;
    }

    /**
     * Set lieuopeninghours.
     *
     * @param string $lieuopeninghours
     *
     * @return Lieu
     */
    public function setLieuopeninghours($lieuopeninghours)
    {
        $this->lieuopeninghours = $lieuopeninghours;

        return $this;
    }

    /**
     * Get lieuopeninghours.
     *
     * @return string
     */
    public function getLieuopeninghours()
    {
        return $this->lieuopeninghours;
    }

    /**
     * Set lieuclosinghours.
     *
     * @param string $lieuclosinghours
     *
     * @return Lieu
     */
    public function setLieuclosinghours($lieuclosinghours)
    {
        $this->lieuclosinghours = $lieuclosinghours;

        return $this;
    }

    /**
     * Get lieuclosinghours.
     *
     * @return string
     */
    public function getLieuclosinghours()
    {
        return $this->lieuclosinghours;
    }

    /**
     * Set lieunumber.
     *
     * @param int|null $lieunumber
     *
     * @return Lieu
     */
    public function setLieunumber($lieunumber = null)
    {
        $this->lieunumber = $lieunumber;

        return $this;
    }

    /**
     * Get lieunumber.
     *
     * @return int|null
     */
    public function getLieunumber()
    {
        return $this->lieunumber;
    }

    /**
     * Set lieuimage.
     *
     * @param string|null $lieuimage
     *
     * @return Lieu
     */
    public function setLieuimage($lieuimage = null)
    {
        $this->lieuimage = $lieuimage;

        return $this;
    }

    /**
     * Get lieuimage.
     *
     * @return string|null
     */
    public function getLieuimage()
    {
        return $this->lieuimage;
    }

    /**
     * Set latitude.
     *
     * @param float|null $latitude
     *
     * @return Lieu
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return float|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param float|null $longitude
     *
     * @return Lieu
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return float|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set isfavorite.
     *
     * @param bool|null $isfavorite
     *
     * @return Lieu
     */
    public function setIsfavorite($isfavorite = null)
    {
        $this->isfavorite = $isfavorite;

        return $this;
    }

    /**
     * Get isfavorite.
     *
     * @return bool|null
     */
    public function getIsfavorite()
    {
        return $this->isfavorite;
    }
}
