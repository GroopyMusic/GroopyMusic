<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SuggestionBox
 *
 * @ORM\Table(name="suggestion_box")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SuggestionBoxRepository")
 */
class SuggestionBox
{
    public function __construct(){
        $this->date = new \Datetime();
    }

    public function getDisplayName() {
        return $this->firstname . ' ' . $this->name;
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
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=255)
     */
    private $object;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mailCopy", type="boolean")
     */
    private $mailCopy;

    /**
     * @ORM\ManyToOne(targetEntity="SuggestionTypeEnum")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

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
     * @return SuggestionBox
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
     * Set name
     *
     * @param string $name
     *
     * @return SuggestionBox
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return SuggestionBox
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return SuggestionBox
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set object
     *
     * @param string $object
     *
     * @return SuggestionBox
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return SuggestionBox
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set mailCopy
     *
     * @param boolean $mailCopy
     *
     * @return SuggestionBox
     */
    public function setMailCopy($mailCopy)
    {
        $this->mailCopy = $mailCopy;

        return $this;
    }

    /**
     * Get mailCopy
     *
     * @return boolean
     */
    public function getMailCopy()
    {
        return $this->mailCopy;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\SuggestionTypeEnum $type
     *
     * @return SuggestionBox
     */
    public function setType(\AppBundle\Entity\SuggestionTypeEnum $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\SuggestionTypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return SuggestionBox
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        $this->name = $user->getLastname();
        $this->firstname = $user->getFirstname();
        $this->email = $user->getEmail();

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
