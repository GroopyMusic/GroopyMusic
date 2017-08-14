<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ContractArtist
 *
 * @ORM\Table(name="contract_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtistRepository")
 */
class ContractArtist
{
    public function __construct() {
        $this->accept_conditions = false;
        $this->reminders = 0;
        $this->date = new \DateTime();
        $this->preferences = new ContractArtistPreferences();
        $this->collected_amount = 0;
    }

    public function addAmount($amount) {
        $this->collected_amount += $amount;
    }

    public function getNbAvailable(CounterPart $cp) {
        $nb = $cp->getMaximumAmount();

        foreach($this->contractsFan as $cf) {
            foreach($cf->getPurchases() as $purchase) {
                if($purchase->getCounterPart()->getId() == $cp->getId()) {
                    $nb -= $purchase->getQuantity();
                }
            }
        }

        if($nb <= 0) return 0;
        return $nb;
    }

    public function cantAddPurchase($quantity, CounterPart $cp) {
        return $this->getNbAvailable($cp) < $quantity;
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
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime")
     */
    private $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity="Step")
     * @ORM\JoinColumn(nullable=false)
     */
    private $step;

    /**
     * @ORM\ManyToOne(targetEntity="Artist")
     * @ORM\JoinColumn(nullable=false)
     */
    private $artist;

    /**
     * @ORM\Column(name="motivations", type="text", nullable=true)
     */
    private $motivations;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="contractArtist")
     */
    private $payments;

    /**
     * @ORM\Column(name="reminders", type="smallint")
     */
    private $reminders;

    /**
     * @ORM\OneToOne(targetEntity="ContractArtistPreferences", cascade={"persist"})
     */
    private $preferences;

    /**
     * @ORM\Column(name="collected_amount", type="integer")
     */
    private $collected_amount;

    /**
     * @ORM\OneToMany(targetEntity="ContractFan", mappedBy="contractArtist")
     */
    private $contractsFan;

    // Conditions approval (form only)
    /**
     * @Assert\NotBlank(message="accept_conditions.notblank")
     */
    private $accept_conditions;

    // Deadline calculation
    private $theoritical_deadline;

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
     * @return ContractArtist
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
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return ContractArtist
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return ContractArtist
     */
    public function setStep(\AppBundle\Entity\Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \AppBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return ContractArtist
     */
    public function setArtist(\AppBundle\Entity\Artist $artist)
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

    /**
     * @return mixed
     */
    public function getTheoriticalDeadline()
    {
        return $this->theoritical_deadline;
    }

    /**
     * @param mixed $theoritical_deadline
     */
    public function setTheoriticalDeadline($theoritical_deadline)
    {
        $this->theoritical_deadline = $theoritical_deadline;
    }

    /**
     * @return boolean
     */
    public function getAcceptConditions()
    {
        return $this->accept_conditions;
    }

    /**
     * @param boolean $accept_conditions
     */
    public function setAcceptConditions($accept_conditions)
    {
        $this->accept_conditions = $accept_conditions;
    }

    /**
     * Set motivations
     *
     * @param string $motivations
     *
     * @return ContractArtist
     */
    public function setMotivations($motivations)
    {
        $this->motivations = $motivations;

        return $this;
    }

    /**
     * Get motivations
     *
     * @return string
     */
    public function getMotivations()
    {
        return $this->motivations;
    }

    /**
     * Add payment
     *
     * @param \AppBundle\Entity\Payment $payment
     *
     * @return ContractArtist
     */
    public function addPayment(\AppBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \AppBundle\Entity\Payment $payment
     */
    public function removePayment(\AppBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set reminders
     *
     * @param integer $reminders
     *
     * @return ContractArtist
     */
    public function setReminders($reminders)
    {
        $this->reminders = $reminders;

        return $this;
    }

    /**
     * Get reminders
     *
     * @return integer
     */
    public function getReminders()
    {
        return $this->reminders;
    }

    /**
     * Set preferences
     *
     * @param \AppBundle\Entity\ContractArtistPreferences $preferences
     *
     * @return ContractArtist
     */
    public function setPreferences(\AppBundle\Entity\ContractArtistPreferences $preferences = null)
    {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * Get preferences
     *
     * @return \AppBundle\Entity\ContractArtistPreferences
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * Set collectedAmount
     *
     * @param integer $collectedAmount
     *
     * @return ContractArtist
     */
    public function setCollectedAmount($collectedAmount)
    {
        $this->collected_amount = $collectedAmount;

        return $this;
    }

    /**
     * Get collectedAmount
     *
     * @return integer
     */
    public function getCollectedAmount()
    {
        return $this->collected_amount;
    }
}
