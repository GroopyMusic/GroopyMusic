<?php

// TODO
// Virer pseudo -> form
// newsletter -> form
// Adresse facultative -> form
// Historique fan -> profil

namespace AppBundle\Entity;

use Azine\EmailBundle\Entity\RecipientInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements RecipientInterface
{
    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function __construct()
    {
        parent::__construct();
        $this->setNotificationMode(RecipientInterface::NOTIFICATION_MODE_IMMEDIATELY);
        $this->setNewsletter(true);
        $this->credits = 0;
        $this->addRole("ROLE_FAN");
        $this->inscription_date = new \DateTime();
    }

    public function owns(Artist $artist) {
        foreach($this->artists_user as $au) {
            if($au->getArtist() == $artist) {
                return true;
            }
        }
        return false;
    }

    public function removeCredits($n) {
        $this->credits -= $n;
    }

    public function addCredits($n) {
        $this->credits += $n;
    }

    /**
     * @return string representation of this user
     */
    public function getDisplayName()
    {
        $firstName = $this->getFirstname();
        $lastName = $this->getLastname();

        $displayName = $firstName . ' ' . $lastName;

        return ucwords($displayName);
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
     * @ORM\OneToMany(targetEntity="SpecialPurchase", mappedBy="user")
     */
    private $specialPurchases;

    /**
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="user")
     */
    private $carts;

    /**
     * @ORM\Column(name="credits", type="integer")
     */
    private $credits;

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
     * @ORM\Column(name="newsletter", type="boolean")
     */
    protected $newsletter;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="user")
     */
    protected $payments;

    /**
     * @ORM\Column(name="stripe_customer_id", type="string", length=255, nullable=true)
     */
    protected $stripe_customer_id;

    /**
     * @ORM\OneToMany(targetEntity="Artist_User", mappedBy="user")
     */
    protected $artists_user;

    /**
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;

    /**
     * @ORM\OneToOne(targetEntity="Address")
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(name="inscription_date", type="datetime")
     */
    private $inscription_date;

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



    /**
     * Set stripeCustomerId
     *
     * @param string $stripeCustomerId
     *
     * @return User
     */
    public function setStripeCustomerId($stripeCustomerId)
    {
        $this->stripe_customer_id = $stripeCustomerId;

        return $this;
    }

    /**
     * Get stripeCustomerId
     *
     * @return string
     */
    public function getStripeCustomerId()
    {
        return $this->stripe_customer_id;
    }

    /**
     * Add specialPurchase
     *
     * @param \AppBundle\Entity\SpecialPurchase $specialPurchase
     *
     * @return UserFan
     */
    public function addSpecialPurchase(\AppBundle\Entity\SpecialPurchase $specialPurchase)
    {
        $this->specialPurchases[] = $specialPurchase;

        return $this;
    }

    /**
     * Remove specialPurchase
     *
     * @param \AppBundle\Entity\SpecialPurchase $specialPurchase
     */
    public function removeSpecialPurchase(\AppBundle\Entity\SpecialPurchase $specialPurchase)
    {
        $this->specialPurchases->removeElement($specialPurchase);
    }

    /**
     * Get specialPurchases
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecialPurchases()
    {
        return $this->specialPurchases;
    }

    /**
     * Add cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return UserFan
     */
    public function addCart(\AppBundle\Entity\Cart $cart)
    {
        $this->carts[] = $cart;

        return $this;
    }

    /**
     * Remove cart
     *
     * @param \AppBundle\Entity\Cart $cart
     */
    public function removeCart(\AppBundle\Entity\Cart $cart)
    {
        $this->carts->removeElement($cart);
    }

    /**
     * Get carts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarts()
    {
        return $this->carts;
    }

    /**
     * Set credits
     *
     * @param integer $credits
     *
     * @return UserFan
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get credits
     *
     * @return integer
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Add artistsUser
     *
     * @param \AppBundle\Entity\Artist_User $artistsUser
     *
     * @return User
     */
    public function addArtistsUser(\AppBundle\Entity\Artist_User $artistsUser)
    {
        $this->artists_user[] = $artistsUser;

        return $this;
    }

    /**
     * Remove artistsUser
     *
     * @param \AppBundle\Entity\Artist_User $artistsUser
     */
    public function removeArtistsUser(\AppBundle\Entity\Artist_User $artistsUser)
    {
        $this->artists_user->removeElement($artistsUser);
    }

    /**
     * Get artistsUser
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArtistsUser()
    {
        return $this->artists_user;
    }

    /**
     * Add genre
     *
     * @param \AppBundle\Entity\Genre $genre
     *
     * @return User
     */
    public function addGenre(\AppBundle\Entity\Genre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    /**
     * Remove genre
     *
     * @param \AppBundle\Entity\Genre $genre
     */
    public function removeGenre(\AppBundle\Entity\Genre $genre)
    {
        $this->genres->removeElement($genre);
    }

    /**
     * Get genres
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * Set inscriptionDate
     *
     * @param \DateTime $inscriptionDate
     *
     * @return User
     */
    public function setInscriptionDate($inscriptionDate)
    {
        $this->inscription_date = $inscriptionDate;

        return $this;
    }

    /**
     * Get inscriptionDate
     *
     * @return \DateTime
     */
    public function getInscriptionDate()
    {
        return $this->inscription_date;
    }

    /**
     * Set address
     *
     * @param \AppBundle\Entity\Address $address
     *
     * @return User
     */
    public function setAddress(\AppBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \AppBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}
