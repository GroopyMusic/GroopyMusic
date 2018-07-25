<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Cart;
use AppBundle\Entity\PhysicalPersonInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="yb_order")
 **/
class YBOrder implements PhysicalPersonInterface
{
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getDisplayName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Cart
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Cart", inversedBy="yb_order")
     */
    private $cart;

    /**
     * @ORM\Column(name="last_name", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $last_name;

    /**
     * @ORM\Column(name="first_name", type="string", length=50)
     * @Assert\NotBlank()
     */
    private $first_name;

    /**
     * @ORM\Column(name="email", type="string", length=60)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return YBOrder
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return YBOrder
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return YBOrder
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return YBOrder
     */
    public function setCart(\AppBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \AppBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Get id
     *
     * @return integer
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
     * @return YBOrder
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
}
