<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\YB\EnumRole;

/**
* @ORM\Entity
* @ORM\Table(name="yb_organization_participations",
    uniqueConstraints={
        @ORM\UniqueConstraint(name="user_organization_unique", columns={"member_id", "organization_id"})
    })
*/
class Membership {

    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    protected $id;

    /**
    * @ORM\Column(type="boolean", name="isAdmin")
    */
    protected $isMemberAdmin;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="participations", cascade={"persist"})
    * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=FALSE)
    */
    protected $member;

    /**
    * @ORM\ManyToOne(targetEntity="Organization", inversedBy="participations")
    * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=FALSE)
    */
    protected $organization;

    // getters and setters
    
    public function getId(){
        return $this->id;
    }

    public function getMember(){
        return $this->member;
    }

    public function setMember(\AppBundle\Entity\User $member = null){
        $this->member = $member;
    }

    public function getOrganization(){
        return $this->organization;
    }

    public function setOrganization(\AppBundle\Entity\YB\Organization $organization){
        $this->organization = $organization;
    }

    public function isAdmin(){
        return $this->isMemberAdmin;
    }

    public function setAdmin($isAdmin){
        $this->isMemberAdmin = $isAdmin;
    }

}