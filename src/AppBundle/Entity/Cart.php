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
    public function __toString()
    {
        $str = 'Panier ';

        if($this->user != null) {
            $str .= 'de ' . $this->user;
        }
        else {
            $str .= 'anonyme ';
        }
        if($this->paid) {
            $str .= ' payé le ' . $this->date_creation->format('d/m/Y');
        }

        return $str . ' (valeur : ' . $this->getAmount() . ' €)';
    }

    public function __construct()
    {
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->confirmed = false;
        $this->paid = false;
        $this->date_creation = new \Datetime();
    }

    /** @return ContractFan */
    public function getFirst() {
        return $this->contracts->first();
    }

    // For single-contract cart
    public function getState() {
        $contractFan = $this->contracts->first();

        if($contractFan->getRefunded()) {
            return 'Remboursé';
        }

        else {
            return 'Payé';
        }
    }

    // TODO don't forget that counterparts have a maximum amount ; set to 10000 in DB
    public function isProblematic() {
        return false;
        foreach($this->contracts as $contract) {
            $contract_artist = $contract->getContractArtist();
            foreach($contract->getPurchases() as $purchase) {
                if($contract_artist->cantAddPurchase($purchase->getQuantity(), $purchase->getCounterPart())) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getAmount() {
        return array_sum(array_map(function($contract) {
            /** @var ContractFan $contract */
            return $contract->getAmount();
        }, $this->contracts->toArray()));
    }

    public function getNbArticles() {
        return array_sum(array_map(function($contract) {
            /** @var ContractFan $contract */
            return count($contract->getPurchases());
        }, $this->contracts->toArray()));
    }

    public function hasContract(ContractFan $cf) {
        return $this->contracts->contains($cf);
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
     * @ORM\OneToMany(targetEntity="ContractFan", mappedBy="cart", cascade={"all"})
     */
    private $contracts;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="carts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\OneToOne(targetEntity="Payment", mappedBy="cart")
     */
    private $payment;

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
        $contract->setCart($this);

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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Cart
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

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

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Cart
     */
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }
}
