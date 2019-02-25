<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Address;
use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Purchase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtist
 *
 * @ORM\Table(name="yb_invoice")
 * @ORM\Entity
 */
class YBInvoice
{
    public function __construct()
    {
        $this->date_generated = new \DateTime();
        $this->user_validated = false;
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\YBContractArtist", inversedBy="invoices")
     */
    private $campaign;

    /**
     * @var bool
     * @ORM\Column(name="user_validated", type="boolean")
     */
    private $user_validated;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_generated", type="datetime")
     */
    private $date_generated;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_limit", type="datetime", nullable=true)
     */
    private $date_limit;

    /**
     * @var Purchase[] $purchases
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Purchase", cascade={"all"}, mappedBy="invoice")
     */
    private $purchases;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @var YBContractArtist $campaign
     */
    public function setCampaign($campaign){
        $this->campaign = $campaign;
        if ($campaign->isPassed()){
            $this->date_limit = new \DateTime();
        } else {
            $this->date_limit = new \DateTime("first day of this month midnight");
        }
    }

    /**
     * @return bool
     */
    public function isUserValidated()
    {
        return $this->user_validated;
    }

    public function validate(){
        $this->user_validated = true;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateGenerated()
    {
        return $this->date_generated;
    }

    /**
     * @return Purchase[]
     */
    public function getPurchases(){
        return $this->purchases;
    }
}
