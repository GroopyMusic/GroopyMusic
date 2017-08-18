<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtist_Artist
 *
 * @ORM\Table(name="contract_artist__artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtist_ArtistRepository")
 */
class ContractArtist_Artist
{
    public function __construct()
    {
        $this->announced = false;
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
     * @var bool
     *
     * @ORM\Column(name="announced", type="boolean")
     */
    private $announced;

    /**
     * @ORM\ManyToOne(targetEntity="ContractArtist", inversedBy="coartists_list")
     */
    private $contract;

    /**
     * @ORM\ManyToOne(targetEntity="Artist")
     */
    private $artist;

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
     * Set announced
     *
     * @param boolean $announced
     *
     * @return ContractArtist_Artist
     */
    public function setAnnounced($announced)
    {
        $this->announced = $announced;

        return $this;
    }

    /**
     * Get announced
     *
     * @return bool
     */
    public function getAnnounced()
    {
        return $this->announced;
    }

    /**
     * Set contract
     *
     * @param \AppBundle\Entity\ContractArtist $contract
     *
     * @return ContractArtist_Artist
     */
    public function setContract(\AppBundle\Entity\ContractArtist $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \AppBundle\Entity\ContractArtist
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return ContractArtist_Artist
     */
    public function setArtist(\AppBundle\Entity\Artist $artist = null)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return \AppBundle\Entity\Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }
}
