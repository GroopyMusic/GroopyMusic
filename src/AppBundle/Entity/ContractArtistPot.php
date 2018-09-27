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

    /**
     * Get noThreshold
     *
     * @return boolean
     */
    public function getNoThreshold()
    {
        return $this->no_threshold;
    }

    /**
     * Add counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     *
     * @return ContractArtistPot
     */
    public function addCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counterParts[] = $counterPart;

        return $this;
    }

    /**
     * Set globalSoldout
     *
     * @param integer $globalSoldout
     *
     * @return ContractArtistPot
     */
    public function setGlobalSoldout($globalSoldout)
    {
        $this->global_soldout = $globalSoldout;

        return $this;
    }

    /**
     * Get globalSoldout
     *
     * @return integer
     */
    public function getGlobalSoldout()
    {
        return $this->global_soldout;
    }

    /**
     * Set photo
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return ContractArtistPot
     */
    public function setPhoto(\AppBundle\Entity\Photo $photo = null)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return \AppBundle\Entity\Photo
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Add campaignPhoto
     *
     * @param \AppBundle\Entity\Photo $campaignPhoto
     *
     * @return ContractArtistPot
     */
    public function addCampaignPhoto(\AppBundle\Entity\Photo $campaignPhoto)
    {
        $this->campaign_photos[] = $campaignPhoto;

        return $this;
    }

    /**
     * Remove campaignPhoto
     *
     * @param \AppBundle\Entity\Photo $campaignPhoto
     */
    public function removeCampaignPhoto(\AppBundle\Entity\Photo $campaignPhoto)
    {
        $this->campaign_photos->removeElement($campaignPhoto);
    }

    /**
     * Get campaignPhotos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampaignPhotos()
    {
        return $this->campaign_photos;
    }

    /**
     * Set threshold
     *
     * @param integer $threshold
     *
     * @return ContractArtistPot
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Get threshold
     *
     * @return integer
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Set counterpartsSold
     *
     * @param float $counterpartsSold
     *
     * @return ContractArtistPot
     */
    public function setCounterpartsSold($counterpartsSold)
    {
        $this->counterparts_sold = $counterpartsSold;

        return $this;
    }

    /**
     * Get counterpartsSold
     *
     * @return float
     */
    public function getCounterpartsSold()
    {
        return $this->counterparts_sold;
    }

    /**
     * Add volunteerProposal
     *
     * @param \AppBundle\Entity\VolunteerProposal $volunteerProposal
     *
     * @return ContractArtistPot
     */
    public function addVolunteerProposal(\AppBundle\Entity\VolunteerProposal $volunteerProposal)
    {
        $this->volunteer_proposals[] = $volunteerProposal;

        return $this;
    }

    /**
     * Remove volunteerProposal
     *
     * @param \AppBundle\Entity\VolunteerProposal $volunteerProposal
     */
    public function removeVolunteerProposal(\AppBundle\Entity\VolunteerProposal $volunteerProposal)
    {
        $this->volunteer_proposals->removeElement($volunteerProposal);
    }

    /**
     * Get volunteerProposals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVolunteerProposals()
    {
        return $this->volunteer_proposals;
    }
}
