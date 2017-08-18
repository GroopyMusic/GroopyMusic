<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConcertPossibilityRepository")
 */
class ConcertPossibility extends ContractArtistPossibility
{
    public function __toString()
    {
        return 'Salle : ' . $this->hall . ' ; date : ' . $this->date;
    }

    /**
     * @var \AppBundle\Entity\Hall
     * @ORM\ManyToOne(targetEntity="Hall")
     */
    private $hall;

    /**
     * Set hall
     *
     * @param \AppBundle\Entity\Hall $hall
     *
     * @return ConcertPossibility
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
