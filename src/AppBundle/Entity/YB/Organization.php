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

/**
 * @ORM\Entity
 * @ORM\Table(name="yb_organization")
 **/
class Organization {

    public function __construct(){
        $this->participations = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Participation", mappedBy="organization", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $participations;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBContractArtist", mappedBy="organization", cascade={"persist"})
     */
    private $campaigns;

    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getParticipations(){
        $this->getParticipations->toArray();
    }

    public function addParticipation(\AppBundle\Entity\YB\Participation $participation){
        if (!$this->participations->contains($participation)){
            $this->participations->add($participation);
            $participation->setOrganization($this);
        }
        return $this;
    }

    public function removeParticipation(\AppBundle\Entity\YB\Participation $participation){
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

    public function getNonSuperAdminMembers(){
        $nonSuperAdminMembers = [];
        foreach ($this->participations as $part){
            if ($part->getRole() !== EnumRole::SUPER_ADMIN){
                $nonSuperAdminMembers[] = $part->getMember();
            }
        }
        return $nonSuperAdminMembers;
    }

    public function hasOnlyOneMember(){
        return count($this->getNonSuperAdminMembers()) === 1;
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
}