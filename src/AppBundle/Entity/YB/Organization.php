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
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\OrganizationRepository")
 * @ORM\Table(name="yb_organization")
 **/
class Organization implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\SoftDeletable\SoftDeletable;

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


    // CONSTRUCT

    public function __construct(){
        $this->participations = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
        $this->published = false;
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
}