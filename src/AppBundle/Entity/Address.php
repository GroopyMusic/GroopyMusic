<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AddressRepository")
 */
class Address
{
    public function __construct()
    {
        $this->country = 'BE';
    }

    public function getNaturalStreet() {
        return $this->number . ' ' . $this->street;
    }

    public function getNatural($parenthesis = null) {
        $string = $this->street . ' ' . $this->number . ', ' . $this->zipcode . ' ' . $this->city;
        if($parenthesis != null) {
            $string .= ' (' . $parenthesis . ')';
        }
        return $string;
    }

    public function getNaturalWithCountry($parenthesis = null){
        $string = $this->street . ' ' . $this->number . ', ' . $this->zipcode . ' ' . $this->city . ' ' . $this->country;
        if ($parenthesis != null){
            $string .= ' (' . $parenthesis . ')';
        }
        return $string;
    }

    public function __toString()
    {
        $name = $this->name != null ? $this->name . ', ' : '';
        return $name . '' . $this->street . ' ' . $this->number . ', ' . $this->zipcode . ' ' . $this->city; // . ' (' . $this->country . ')';
    }

    public function equals(Address $other){
        if (strtolower($this->street) !== strtolower($other->getStreet())){
            return false;
        }
        if (strtolower($this->zipcode) !== strtolower($other->getZipcode())){
            return false;
        }
        if (strtolower($this->country) !== strtolower($other->getCountry())){
            return false;
        }
        if (strtolower($this->number) !== strtolower($other->getNumber())){
            return false;
        }
        if (strtolower($this->city) !== strtolower($other->getCity())){
            return false;
        }
        return true;
    }

    public function isGeolocalizable(){
        return $this->latitude !== null && $this->longitude !== null;
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
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=10, nullable=true)
     */
    private $zipcode;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=20, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=10, nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var
     * @ORM\Column(name="latitude", type="decimal", precision=15, scale=10, nullable=true)
     */
    private $latitude;

    /**
     * @var
     * @ORM\Column(name="longitude", type="decimal", precision=15, scale=10, nullable=true)
     */
    private $longitude;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     *
     * @return Address
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Address
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
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


}
