<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtistPreferences
 *
 * @ORM\Table(name="contract_artist_possibility")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtistPossibilityRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"concert" = "ConcertPossibility", "possibility" = "ContractArtistPossibility"})
 */
class ContractArtistPossibility
{
    public function __toString()
    {
        if($this instanceof ConcertPossibility) {
            return $this->__toString();
        }
        else {
            return 'Possibility';
        }
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_info", type="text", nullable=true)
     */
    protected $additional_info;

    // Unmapped (set by BaseContractArtist setters)
    /** @var BaseContractArtist $contract */
    protected $contract;
    public function getContract() {return $this->contract;}
    public function setContract(BaseContractArtist $contract) {$this->contract = $contract; return $this;}

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
     * @return ContractArtistPossibility
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
     * @return ContractArtistPossibility
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additional_info = $additionalInfo;

        return $this;
    }

    /**
     * Get additionalInfo
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }
}
