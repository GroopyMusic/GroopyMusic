<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 25/04/2018
 * Time: 10:47
 */

namespace AppBundle\Entity;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * SponsorshipInvitation
 *
 * @ORM\Table(name="sponsorship_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SponsorshipInvitationRepository")
 */
class SponsorshipInvitation
{

    /**
     * SponsorshipInvitation constructor.
     * @param \DateTime $date_invitation
     * @param String $email_invitation
     * @param $text_invitation
     * @param $host_invitation
     * @param $target_invitation
     * @param $contract_artist
     * @param bool $reward_sent
     */
    public function __construct(\DateTime $date_invitation = null, $email_invitation = null, $text_invitation = null, $host_invitation = null, $contract_artist = null, $token = null)
    {
        $this->date_invitation = $date_invitation;
        $this->email_invitation = $email_invitation;
        $this->text_invitation = $text_invitation;
        if ($host_invitation != null) {
            $host_invitation->addSponsorship($this);
        }
        $this->target_invitation = null;
        $this->contract_artist = $contract_artist;
        $this->reward_sent = 0;
        $this->token_sponsorship = $token;
        $this->last_date_acceptation = null;
    }

    public function __toString()
    {
        return "Invitation de " . $this->host_invitation->getDisplayName() . " à " . $this->email_invitation;
    }

    /** @return bool */
    public function isConfirmed() {
        if ($this->getTargetInvitation() == null) {
            return false;
        } else {
            return $this->getTargetInvitation()->getDeleted() === false;
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
     * @ORM\Column(name="date_invitation", type="datetime")
     */
    private $date_invitation;

    /**
     * @var String
     *
     * @ORM\Column(name="email_invitation", type="string")
     */
    private $email_invitation;

    /**
     * @ORM\Column(name="text_invitation", type="text", nullable=true)
     */
    private $text_invitation;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sponsorships")
     * @ORM\JoinColumn(nullable=false)
     */
    private $host_invitation;


    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="sponsorship_invitation")
     * @ORM\JoinColumn(nullable=true)
     */
    private $target_invitation;

    /**
     * @ORM\ManyToOne(targetEntity="ContractArtist")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contract_artist;

    /**
     * @var boolean
     *
     * @ORM\Column(name="reward_sent", type="boolean")
     */
    private $reward_sent;

    /**
     * @var String
     *
     * @ORM\Column(name="token_sponsorship", type="string")
     */
    private $token_sponsorship;

    /**
     * @ORM\Column(name="last_date_acceptation", type="datetime",nullable=true)
     */
    private $last_date_acceptation;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateInvitation
     *
     * @param \DateTime $dateInvitation
     *
     * @return SponsorshipInvitation
     */
    public function setDateInvitation($dateInvitation)
    {
        $this->date_invitation = $dateInvitation;

        return $this;
    }

    /**
     * Get dateInvitation
     *
     * @return \DateTime
     */
    public function getDateInvitation()
    {
        return $this->date_invitation;
    }

    /**
     * Set emailInvitation
     *
     * @param string $emailInvitation
     *
     * @return SponsorshipInvitation
     */
    public function setEmailInvitation($emailInvitation)
    {
        $this->email_invitation = $emailInvitation;

        return $this;
    }

    /**
     * Get emailInvitation
     *
     * @return string
     */
    public function getEmailInvitation()
    {
        return $this->email_invitation;
    }

    /**
     * Set textInvitation
     *
     * @param string $textInvitation
     *
     * @return SponsorshipInvitation
     */
    public function setTextInvitation($textInvitation)
    {
        $this->text_invitation = $textInvitation;

        return $this;
    }

    /**
     * Get textInvitation
     *
     * @return string
     */
    public function getTextInvitation()
    {
        return $this->text_invitation;
    }

    /**
     * Set hostInvitation
     *
     * @param User $hostInvitation
     *
     * @return SponsorshipInvitation
     */
    public function setHostInvitation(User $hostInvitation)
    {
        $this->host_invitation = $hostInvitation;

        return $this;
    }

    /**
     * Get hostInvitation
     *
     * @return User
     */
    public function getHostInvitation()
    {
        return $this->host_invitation;
    }

    /**
     * Set targetInvitation
     *
     * @param User $targetInvitation
     *
     * @return SponsorshipInvitation
     */
    public function setTargetInvitation(User $targetInvitation = null)
    {
        $this->target_invitation = $targetInvitation;

        return $this;
    }

    /**
     * Get targetInvitation
     *
     * @return User
     */
    public function getTargetInvitation()
    {
        return $this->target_invitation;
    }

    /**
     * Set contractArtist
     *
     * @param ContractArtist $contractArtist
     *
     * @return SponsorshipInvitation
     */
    public function setContractArtist(ContractArtist $contractArtist)
    {
        $this->contract_artist = $contractArtist;

        return $this;
    }

    /**
     * Get contractArtist
     *
     * @return ContractArtist
     */
    public function getContractArtist()
    {
        return $this->contract_artist;
    }

    /**
     * Set rewardSent
     *
     * @param boolean $rewardSent
     *
     * @return SponsorshipInvitation
     */
    public function setRewardSent($rewardSent)
    {
        $this->reward_sent = $rewardSent;

        return $this;
    }

    /**
     * Get rewardSent
     *
     * @return boolean
     */
    public function getRewardSent()
    {
        return $this->reward_sent;
    }

    /**
     * Set tokenSponsorship
     *
     * @param string $tokenSponsorship
     *
     * @return SponsorshipInvitation
     */
    public function setTokenSponsorship($tokenSponsorship)
    {
        $this->token_sponsorship = $tokenSponsorship;

        return $this;
    }

    /**
     * Get tokenSponsorship
     *
     * @return string
     */
    public function getTokenSponsorship()
    {
        return $this->token_sponsorship;
    }


    /**
     * Set lastDateAcceptation
     *
     * @param \DateTime $lastDateAcceptation
     *
     * @return SponsorshipInvitation
     */
    public function setLastDateAcceptation($lastDateAcceptation)
    {
        $this->last_date_acceptation = $lastDateAcceptation;

        return $this;
    }

    /**
     * Get lastDateAcceptation
     *
     * @return \DateTime
     */
    public function getLastDateAcceptation()
    {
        return $this->last_date_acceptation;
    }
}
