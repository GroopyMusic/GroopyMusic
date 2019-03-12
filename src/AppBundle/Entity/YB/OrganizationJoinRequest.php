<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\OrganizationJoinRequestRepository")
 * @ORM\Table(name="yb_organization_join_request", uniqueConstraints={
@ORM\UniqueConstraint(name="user_organization_unique", columns={"demander_id", "organization_id", "email"})
})
 **/
class OrganizationJoinRequest{

    // CONSTRUCT

    public function __construct(User $demander, $email, Organization $organization){
        $this->demander = $demander;
        $this->email = $email;
        $this->organization = $organization;
        $this->date = new \DateTime();
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="join_organization_request", cascade={"persist"})
     */
    private $demander;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Organization", inversedBy="join_organization_request", cascade={"persist"})
     */
    private $organization;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    // GETTERS & SETTERS

    /**
     * @return mixed
     */
    public function getDemander()
    {
        return $this->demander;
    }

    /**
     * @param mixed $demander
     */
    public function setDemander($demander)
    {
        $this->demander = $demander;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    public function getDate(){
        return $this->date;
    }



}