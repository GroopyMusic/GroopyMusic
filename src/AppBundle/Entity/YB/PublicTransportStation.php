<?php

namespace AppBundle\Entity\YB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class PublicTransportStation
 * @package AppBundle\Entity\YB
 * @ORM\Table(name="yb_public_transport_stations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\PublicTransportStationRepository")
 */
class PublicTransportStation {

    const SNCB = 'gares-sncb';
    const STIB = 'arrets-stib';

    public function __construct($name, $latitude, $longitude, $type, $distance){
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        switch ($type){
            case self::SNCB : $this->type = 'SNCB'; break;
            case self::STIB : $this->type = 'STIB'; break;
            default : $this->type = 'TEC'; break;
        }
        $this->distance = $distance;
    }

    public function __toString(){
        return $this->name . ' ' . $this->type . ' ' . $this->distance;
    }

    public function timeToWalk(){
        $kmPerHour = 5; // https://en.wikipedia.org/wiki/Preferred_walking_speed
        $minutes = (60 / $kmPerHour) * $this->distance;
        if ($minutes < 1){
            return "< 1min";
        }
        $minutes = round($minutes);
        return $minutes . 'min';
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var
     *
     * @ORM\Column(name="latitude", type="decimal", precision=15, scale=10)
     */
    private $latitude;

    /**
     * @var
     *
     * @ORM\Column(name="longitude", type="decimal", precision=15, scale=10)
     */
    private $longitude;

    /**
     * @var
     *
     * @ORM\Column(name="type", type="string", length=15)
     */
    private $type;

    /**
     * @var
     *
     * @ORM\Column(name="distance", type="decimal", precision=4, scale=3)
     */
    private $distance;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }




}