<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtistPreferences
 *
 * @ORM\Table(name="contract_artist_preferences")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtistPreferencesRepository")
 */
class ContractArtistPreferences
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_info", type="text", nullable=true)
     */
    private $additionalInfo;

    /**
     * @ORM\ManyToOne(targetEntity="Hall")
     */
    private $hall;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ContractArtistPreferences
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set additionalInfo
     *
     * @param string $additionalInfo
     *
     * @return ContractArtistPreferences
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    /**
     * Get additionalInfo
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    /**
     * Set hall
     *
     * @param \AppBundle\Entity\Hall $hall
     *
     * @return ContractArtistPreferences
     */
    public function setHall(\AppBundle\Entity\Hall $hall = null)
    {
        $this->hall = $hall;

        return $this;
    }

    /**
     * Get hall
     *
     * @return \AppBundle\Entity\Hall
     */
    public function getHall()
    {
        return $this->hall;
    }
}
