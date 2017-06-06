<?php

namespace AppBundle\Entity;

use Azine\EmailBundle\Entity\RecipientInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"user_artist" = "UserArtist", "user_fan" = "UserFan"})
 */
abstract class User extends BaseUser implements RecipientInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setNotificationMode(RecipientInterface::NOTIFICATION_MODE_IMMEDIATELY);
        $this->setNewsletter(true);
    }

    /**
     * @return string representation of this user
     */
    public function getDisplayName()
    {
        $firstName = $this->getFirstName();
        $lastName = $this->getLastName();
        $username = $this->getUsername();

        $displayName = $username;
        if ($firstName) {
            $displayName = $firstName;
        } elseif ($lastName) {
            $displayName = $this->getSalutation()." ".$lastName;
        }

        return ucwords($displayName);
    }

    /**
     * @var string
     */
    protected $preferredLocale;

    /**
     * @var string
     */
    protected $salutation;

    /**
     * @var integer
     */
    protected $notification_mode;

    /**
     * @var boolean
     */
    protected $newsletter;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="user")
     */
    protected $payments;

    /**
     * @param mixed $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @param int $notification_mode
     */
    public function setNotificationMode($notification_mode)
    {
        $this->notification_mode = $notification_mode;
    }

    /**
     * @param boolean $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return mixed
     */
    public function getPreferredLocale()
    {
        return $this->preferredLocale;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @return int
     */
    public function getNotificationMode()
    {
        return $this->notification_mode;
    }

    /**
     * @return boolean
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param mixed $preferredLocale
     */
    public function setPreferredLocale($preferredLocale)
    {
        $this->preferredLocale = $preferredLocale;
    }


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="lastname", type="string", length=255)
     * TODO traduire
     * @Assert\NotBlank(message="Please enter your last name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=255,
     *     minMessage="The last name is too short.",
     *     maxMessage="The last name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $lastname;

    /**
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    protected $firstname;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
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
     * Add payment
     *
     * @param \AppBundle\Entity\Payment $payment
     *
     * @return User
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
}
