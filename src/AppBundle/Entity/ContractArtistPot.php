<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="contract_artist_pot")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtistPotRepository")
 */
class ContractArtistPot extends BaseContractArtist
{
    const STATE_PASSED = 'state.passed';
    const STATE_ONGOING = 'state.ongoing';

    const UNCROWDABLE_STATES = [self::STATE_PASSED];
    const SUCCESSFUL_STATES = [self::STATE_ONGOING];

    public function __toString()
    {
        return $this->step->__toString() . ' (' . $this->artist->__toString() . ')';
    }

    public function getState() {
        if(isset($this->state)) {
            return $this->state;
        }
        if($this->dateEnd < (new \DateTime())) {
            return self::STATE_PASSED;
        }
        else {
            return self::STATE_ONGOING;
        }
    }

    public function isCrowdable() {
        return !in_array($this->getState(), self::UNCROWDABLE_STATES);
    }

    public function isUnCrowdable() {
        return !$this->isCrowdable();
    }

    public function getSuccessfulStates() {
        return self::SUCCESSFUL_STATES;
    }

    public function getTotalNbAvailable() {
        return PHP_INT_MAX;
    }

    public function isSoldOut() {
        return false;
    }

    /**
     * @var StepPot
     *
     * @ORM\ManyToOne(targetEntity="StepPot")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $step;

    /**
     * @ORM\Column(name="date_event", type="datetime", nullable=false)
     */
    private $date_event;

    /**
     * @return StepPot
     */
    public function getStep(): StepPot
    {
        return $this->step;
    }

    /**
     * @param StepPot $step
     */
    public function setStep(StepPot $step)
    {
        $this->step = $step;
    }

    /**
     * @return mixed
     */
    public function getDateEvent()
    {
        return $this->date_event;
    }

    /**
     * @param \DateTime $date
     * @return ContractArtistPot
     */
    public function setDateEvent($date_event)
    {
        $this->date_event = $date_event;
        $this->dateEnd = $date_event;

        return $this;
    }
}