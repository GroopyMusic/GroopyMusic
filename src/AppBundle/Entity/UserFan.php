<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 05-02-17
 * Time: 18:46
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_fan")
 * @UniqueEntity(fields = "username", targetClass = "AppBundle\Entity\User", message="fos_user.username.already_used")
 * @UniqueEntity(fields = "email", targetClass = "AppBundle\Entity\User", message="fos_user.email.already_used")
 */
class UserFan extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->addRole("ROLE_FAN");
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="SpecialPurchase", mappedBy="fan")
     */
    private $specialPurchases;

    /**
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="fan")
     */
    private $carts;

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return UserFan
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
     * @return UserFan
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
}
