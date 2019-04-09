<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Gedmo\Mapping\Annotations as Gedmo;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\OrganizationRepository")
 * @ORM\Table(name="yb_organization")
 **/
class Organization {

    use ORMBehaviors\SoftDeletable\SoftDeletable;

    // CONSTRUCT

    public function __construct(){
        $this->participations = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
    }

    // METHODS

    public function addParticipation(\AppBundle\Entity\YB\Membership $participation){
        if (!$this->participations->contains($participation)){
            $this->participations->add($participation);
            $participation->setOrganization($this);
        }
        return $this;
    }

    public function removeParticipation(\AppBundle\Entity\YB\Membership $participation){
        if ($this->participations->contains($participation)){
            $this->participations->removeElement($participation);
            $participation->setOrganization(null);
        }
        return $this;
    }

    public function getMembers(){
        return array_map(
            function ($participation){
                return $participation->getMember();
            },
            $this->participations->toArray()
        );
    }

    public function hasOnlyOneMember(){
        return count($this->getMembers()) <= 1;
    }

    public function hasAtLeastOneAdminLeft(User $quittingMember){
        foreach ($this->participations as $part){
            if ($part->getMember() !== $quittingMember && $part->isAdmin()){
                return true;
            }
        }
        return false;
    }

    public function hasMember(User $member){
        return in_array($member, $this->getMembers());
    }

    public function hasPendingRequest(){
        return count($this->getJoinOrganizationRequest()) > 0;
    }

    public function handleVenue(Venue $venue){
        return in_array($venue, $this->venues);
    }

    // ATTRIBUTES

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Membership", mappedBy="organization", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $participations;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBContractArtist", mappedBy="organization", cascade={"persist"})
     */
    private $campaigns;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Venue", mappedBy="organization", cascade={"persist"})
     */
    private $venues;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\OrganizationJoinRequest", mappedBy="organization", cascade={"persist"})
     */
    private $join_organization_request;

    /**
     * @var bool
     *
     * @ORM\Column(name="isPrivate", type="boolean")
     */
    private $isPrivate = false;

    /**
     * @var string
     * @ORM\Column(name="bank_account", type="string", length=50, nullable=true)
     */
    private $bank_account;

    /**
     * @var string
     * @ORM\Column(name="vat_number", type="string", length=50, nullable=true)
     */
    private $vat_number;

    // GETTERS & SETTERS

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getParticipations()
    {
        return $this->participations;
    }

    /**
     * @param mixed $participations
     */
    public function setParticipations($participations)
    {
        $this->participations = $participations;
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * @param mixed $campaigns
     */
    public function setCampaigns($campaigns)
    {
        $this->campaigns = $campaigns;
    }

    /**
     * @return mixed
     */
    public function getVenues()
    {
        return $this->venues;
    }

    /**
     * @param mixed $venues
     */
    public function setVenues($venues)
    {
        $this->venues = $venues;
    }

    /**
     * @return mixed
     */
    public function getJoinOrganizationRequest()
    {
        return $this->join_organization_request;
    }

    /**
     * @param mixed $join_organization_request
     */
    public function setJoinOrganizationRequest($join_organization_request)
    {
        $this->join_organization_request = $join_organization_request;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @param bool $isPrivate
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;
    }

    /**
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bank_account;
    }

    /**
     * @param string $bank_account
     */
    public function setBankAccount($bank_account)
    {
        $this->bank_account = $bank_account;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vat_number;
    }

    /**
     * @param string $vat_number
     */
    public function setVatNumber(string $vat_number)
    {
        $this->vat_number = $vat_number;
    }
}