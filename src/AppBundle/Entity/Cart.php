<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CartRepository")
 */
class Cart
{
    public function __construct()
    {
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->confirmed = false;
        $this->paid = false;
    }

    public function getAmount() {
        return array_sum(array_map(function($contract) {
            return $contract->getAmount();
        }, $this->contracts->toArray()));
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
     * @var bool
     *
     * @ORM\Column(name="confirmed", type="boolean")
     */
    private $confirmed;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid;

    /**
     * @ORM\OneToMany(targetEntity="ContractFan", mappedBy="cart")
     */
    private $contracts;

    /**
     * @ORM\ManyToOne(targetEntity="UserFan", inversedBy="carts")
     */
    private $fan;


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
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return Cart
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return bool
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return Cart
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Add contract
     *
     * @param \AppBundle\Entity\ContractFan $contract
     *
     * @return Cart
     */
    public function addContract(\AppBundle\Entity\ContractFan $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove contract
     *
     * @param \AppBundle\Entity\ContractFan $contract
     */
    public function removeContract(\AppBundle\Entity\ContractFan $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * Set fan
     *
     * @param \AppBundle\Entity\UserFan $fan
     *
     * @return Cart
     */
    public function setFan(\AppBundle\Entity\UserFan $fan = null)
    {
        $this->fan = $fan;

        return $this;
    }

    /**
     * Get fan
     *
     * @return \AppBundle\Entity\UserFan
     */
    public function getFan()
    {
        return $this->fan;
    }
}
