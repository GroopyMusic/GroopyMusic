<?php

namespace AppBundle\Entity;

use Azine\EmailBundle\Entity\RecipientInterface;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements RecipientInterface, PhysicalPersonInterface
{
    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function __construct()
    {
        parent::__construct();
        $this->setNotificationMode(RecipientInterface::NOTIFICATION_MODE_IMMEDIATELY); // For Azine but not actually used
        $this->setNewsletter(false);
        $this->addRole("ROLE_FAN");
        $this->inscription_date = new \DateTime();
        $this->accept_conditions = false;
        $this->deleted = false;
        $this->rewards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->category_statistics = new ArrayCollection();
        $this->user_conditions = new ArrayCollection();
    }

    public function owns(Artist $artist) {
        foreach($this->artists_user as $au) {
            /** @var Artist_User $au */
            if($au->getArtist() == $artist) {
                return true;
            }
        }
        return false;
    }

    public function anonymize() {
        $code = substr(str_shuffle(date('Ymd') . md5($this->getPassword())), 0, 200) . '@un-mute.be';
        $this->setUsername($code);
        $this->setUsernameCanonical($code);
        $this->setAddress(null);
        $this->setEmail($code);
        $this->setEmailCanonical($code);
        $this->setEnabled(false);
        $this->setLastLogin(null);
        $this->setConfirmationToken(null);
        $this->setPasswordRequestedAt(null);
        $this->setRoles([]);
        $this->setLastname('NO_ONE');
        $this->setFirstname('NO_ONE');
        $this->setNewsletter(false);
        $this->setStripeCustomerId(null);
        $this->setInscriptionDate(null);
        $this->setAskedEmail(null);
        $this->setAskedEmailToken(null);
        $this->setBirthday(null);
        $this->setFacebookAccessToken(null);
        $this->setFacebookId(null);
        $this->setDeleted(true);

        foreach($this->artists_user as $au) {
            $this->removeArtistsUser($au);
        }
    }

    /**
     * @override
     */
    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
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

    public function getArtists() {
        return array_filter(array_map(function(Artist_User $elem) {
            return $elem->getArtist();
        }, $this->artists_user->toArray()), function(Artist $artist) {
            return $artist->isActive();
        });
    }

    public function getArtistsExport() {
        $exportList = array();
        $i = 1;
        foreach ($this->getArtists() as $key => $val) {
            /** @var $val Artist */
            $exportList[] = $i .
                ') ' . $val->getArtistname();
            $i++;
        }
        return '<pre>' . join(PHP_EOL, $exportList) . '</pre>';
    }

    public function getAcceptedConditions() {
        return array_map(function(User_Conditions $uc) {
            return $uc->getConditions();
        }, $this->user_conditions->toArray());
    }

    public function hasAccepted(Conditions $conditions) {
        return in_array($conditions->getId(), array_map(function($elem) { return $elem->getId(); }, $this->getAcceptedConditions()));
    }

    public function isFirstVisit() {
        return $this->user_conditions->isEmpty();
    }

    // Form only
    public $accept_conditions;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    protected $lastname;

    /**
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    protected $firstname;

    /**
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="user")
     */
    protected $carts;

    /**
     * @var string
     * @ORM\Column(name="preferred_locale", type="string", length=10)
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
    protected $genres;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="inscription_date", type="datetime", nullable=true)
     */
    protected $inscription_date;

    /**
     * @ORM\Column(name="asked_email", type="string", length=255, nullable=true)
     */
    protected $asked_email;

    /**
     * @ORM\Column(name="asked_email_token", type="text", nullable=true)
     */
    protected $asked_email_token;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="user")
     */
    protected $notifications;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @ORM\Column(name="deleted", type="boolean")
     */
    protected $deleted;

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebook_id;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /**
     * @ORM\OneToMany(targetEntity="User_Category", mappedBy="user", cascade={"all"}, orphanRemoval=true)
     */
    protected $category_statistics;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="User_Conditions", mappedBy="user")
     */
    protected $user_conditions;

    /**
     * @ORM\OneToMany(targetEntity="User_Reward", mappedBy="user", cascade={"all"}, orphanRemoval=true)
     */
    private $rewards;

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
     * Add cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return User
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
    public function setAddress($address = null)
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

    /**
     * Set address
     *
     * @param \AppBundle\Entity\Address $address
     *
     * @return User
     */
    public function setAddressForm($address = null)
    {
        if(is_array($address))
            if(empty($address)){
                $this->address = null;
            }
            else {
                $this->address = array_pop($address);
            }
        else
            $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \AppBundle\Entity\Address
     */
    public function getAddressForm()
    {
        return $this->address == null ? null : array($this->address);
    }

    /**
     * Set askedEmail
     *
     * @param string $askedEmail
     *
     * @return User
     */
    public function setAskedEmail($askedEmail)
    {
        $this->asked_email = $askedEmail;

        return $this;
    }

    /**
     * Get askedEmail
     *
     * @return string
     */
    public function getAskedEmail()
    {
        return $this->asked_email;
    }

    /**
     * Add notification
     *
     * @param \AppBundle\Entity\Notification $notification
     *
     * @return User
     */
    public function addNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;
        $notification->setUser($this);

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AppBundle\Entity\Notification $notification
     */
    public function removeNotification(\AppBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set askedEmailToken
     *
     * @param string $askedEmailToken
     *
     * @return User
     */
    public function setAskedEmailToken($askedEmailToken)
    {
        $this->asked_email_token = $askedEmailToken;

        return $this;
    }

    /**
     * Get askedEmailToken
     *
     * @return string
     */
    public function getAskedEmailToken()
    {
        return $this->asked_email_token;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return User
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebook_access_token = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * Add categoryStatistic
     *
     * @param \AppBundle\Entity\User_Category $categoryStatistic
     *
     * @return User
     */
    public function addCategoryStatistic(\AppBundle\Entity\User_Category $categoryStatistic)
    {
        $this->category_statistics[] = $categoryStatistic;

        return $this;
    }

    /**
     * Remove categoryStatistic
     *
     * @param \AppBundle\Entity\User_Category $categoryStatistic
     */
    public function removeCategoryStatistic(\AppBundle\Entity\User_Category $categoryStatistic)
    {
        $this->category_statistics->removeElement($categoryStatistic);
    }

    /**
     * Get categoryStatistics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategoryStatistics()
    {
        return $this->category_statistics;
    }

    /**
     * Add reward
     *
     * @param \AppBundle\Entity\User_Reward $reward
     *
     * @return User
     */
    public function addReward(\AppBundle\Entity\User_Reward $reward)
    {
        $this->rewards[] = $reward;

        return $this;
    }

    /**
     * Remove reward
     *
     * @param \AppBundle\Entity\User_Reward $reward
     */
    public function removeReward(\AppBundle\Entity\User_Reward $reward)
    {
        $this->rewards->removeElement($reward);
    }

    /**
     * Get rewards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRewards()
    {
        return $this->rewards;
    }

    /**
     * Add userCondition
     *
     * @param \AppBundle\Entity\User_Conditions $userCondition
     *
     * @return User
     */
    public function addUserCondition(\AppBundle\Entity\User_Conditions $userCondition)
    {
        $this->user_conditions[] = $userCondition;

        return $this;
    }

    /**
     * Remove userCondition
     *
     * @param \AppBundle\Entity\User_Conditions $userCondition
     */
    public function removeUserCondition(\AppBundle\Entity\User_Conditions $userCondition)
    {
        $this->user_conditions->removeElement($userCondition);
    }

    /**
     * Get userConditions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserConditions()
    {
        return $this->user_conditions;
    }
}
