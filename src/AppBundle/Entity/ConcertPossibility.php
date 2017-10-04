<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConcertPossibilityRepository")
 */
class ConcertPossibility extends ContractArtistPossibility
{
    public function setContract(ContractArtist $contract)
    {
        return parent::setContract($contract);
    }

    public function __toString()
    {
        $string = '';

        if($this->hall != null)
            $string .= 'Salle : ' . $this->hall . ' ';

        if($this->date != null)
            $string .= 'Date : ' . $this->date->format('d/m/y');

        return $string;
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
