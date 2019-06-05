<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Photo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\User;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\OrganizationRepository")
 * @ORM\Table(name="yb_organization")
 * @Vich\Uploadable
 **/
class Organization implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\SoftDeletable\SoftDeletable;
    use ORMBehaviors\Sluggable\Sluggable;

    const PHOTOS_DIR = 'images/organizations/';

    public static function getWebPath(Photo $photo) {
        return self::PHOTOS_DIR . $photo->getFilename();
    }

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch (\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
    }

    public function getDefaultLocale()
    {
        return 'fr';
    }

    public function __toString()
    {
        return '' . $this->getName();
    }

    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);
        return $this;
    }

    public function getLocale()
    {
        return $this->getCurrentLocale();
    }

    public function getSluggableFields() {
        return ['name'];
    }


    // CONSTRUCT

    public function __construct(){
        $this->participations = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
        $this->published = false;
        $this->updatedAt = new \DateTime();
    }

    // METHODS

    /**
     * Add a member to the organization
     * @param Membership $participation
     * @return $this
     */
    public function addParticipation(\AppBundle\Entity\YB\Membership $participation){
        if (!$this->participations->contains($participation)){
            $this->participations->add($participation);
            $participation->setOrganization($this);
        }
        return $this;
    }

    /**
     * Remove a member from the organization
     * @param Membership $participation
     * @return $this
     */
    public function removeParticipation(\AppBundle\Entity\YB\Membership $participation){
        if ($this->participations->contains($participation)){
            $this->participations->removeElement($participation);
            //$participation->setOrganization(null);
        }
        return $this;
    }

    /**
     * Get the list of the members
     * @return array
     */
    public function getMembers(){
        return array_map(
            function ($participation){
                return $participation->getMember();
            },
            $this->participations->toArray()
        );
    }

    /**
     * Checks if the organization has at most one member
     * @return bool
     */
    public function hasOnlyOneMember(){
        return count($this->getMembers()) <= 1;
    }

    /**
     * Checks if the organization has at least one admin
     * @param User $quittingMember
     * @return bool
     */
    public function hasAtLeastOneAdminLeft(User $quittingMember){
        foreach ($this->participations as $part){
            if ($part->getMember() !== $quittingMember && $part->isAdmin()){
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the given user is a member of the organization
     * @param User $member
     * @return bool
     */
    public function hasMember(User $member){
        return in_array($member, $this->getMembers());
    }

    /**
     * Checks if the organization has pending request
     * A pending request is generate when a member invites another user in the organization
     * and that user has still not confirmed its membership
     * @return bool
     */
    public function hasPendingRequest(){
        return count($this->getJoinOrganizationRequest()) > 0;
    }

    private $ongoingCampaigns = null;
    public function getOngoingCampaigns() {
        if($this->ongoingCampaigns == null) {
            $this->ongoingCampaigns = array_filter($this->campaigns->toArray(), function(YBContractArtist $campaign) {
                return $campaign->isOngoing();
            });
        }
        return $this->ongoingCampaigns;
    }

    /**
     * Checks if the given venue is handled by the organization
     * @param Venue $venue
     * @return bool
     */
    public function handleVenue(Venue $venue){
        return in_array($venue, $this->venues);
    }

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="yb_organization_header", fileNameProperty="fileName", size="imageSize")
     *
     * @var File
     */
    private $imageFile;
    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;
    private $filename;
    private $imageSize;

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        // It is required that at least one field changes if you are using doctrine
        // otherwise the event listeners won't be called and the file is lost
        $this->updatedAt = new \DateTime();
    }

    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }

    public function getFilename() {
        return $this->filename;
    }

    public function getImageSize() {
        return $this->imageSize;
    }

    public function getImageFile() {
        return $this->imageFile;
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
     * @ORM\Column(name="published", type="boolean")
     */
    private $published;

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

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Photo", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     */
    protected $photo;

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

    public function setPublished($published) {
        $this->published = $published;
        return $this;
    }
    public function getPublished() {
        return $this->published;
    }
    public function isPublished() {
        return $this->getPublished();
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }
}