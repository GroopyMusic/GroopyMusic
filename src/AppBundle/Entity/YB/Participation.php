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
class Participation {

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
    * @ORM\Column(type="integer", name="role")
    */
    protected $role;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="participations")
    * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=FALSE)
    */
    protected $member;

    /**
    * @ORM\ManyToOne(targetEntity="Organization", inversedBy="members")
    * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=FALSE)
    */
    protected $organization;

    /**
    * @ORM\Column(type="boolean", name="isPending")
    */
    protected $isPending = false;

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

    public function getRole(){
        return $this->role;
    }

    public function setRole(){
        if (!$this->isAdmin()){
            if ($this->getMember()->hasRole("ROLE_SUPER_ADMIN")){
                $this->role = EnumRole::SUPER_ADMIN;
            } else {
                $this->role = EnumRole::MEMBER;
            }
        } else {
            if ($this->getMember()->hasRole("ROLE_SUPER_ADMIN")){
                $this->role = EnumRole::ADMIN_AND_SUPER_ADMIN;
            } else {
                $this->role = EnumRole::ADMIN;
            }
        }
    }

    public function isPending(){
        return $this->isPending;
    }

    public function setPending($isPending){
        $this->isPending = $isPending;
    }

}