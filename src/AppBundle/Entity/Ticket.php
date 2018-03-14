<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 30-11-17
 * Time: 15:51
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ticket
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TicketRepository")
 */
class Ticket
{
    public function __construct(ContractFan $cf, CounterPart $counterPart, $num, $price)
    {
        $this->contractFan = $cf;
        $this->barcode_text = $cf->getBarcodeText() . '' . $num;
        $this->counterPart = $counterPart;
        $this->price = $price;
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
     * @ORM\Column(name="barcode_text", type="string", length=255)
     */
    private $barcode_text;

    /**
     * @ORM\ManyToOne(targetEntity="ContractFan", inversedBy="tickets")
     */
    private $contractFan;

    /**
     * @ORM\ManyToOne(targetEntity="CounterPart")
     */
    private $counterPart;

    /**
     * @ORM\Column(name="price", type="smallint")
     */
    private $price;

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
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return Ticket
     */
    public function setBarcodeText($barcodeText)
    {
        $this->barcode_text = $barcodeText;

        return $this;
    }

    /**
     * Get barcodeText
     *
     * @return string
     */
    public function getBarcodeText()
    {
        return $this->barcode_text;
    }

    /**
     * Set contractFan
     *
     * @param \AppBundle\Entity\ContractFan $contractFan
     *
     * @return Ticket
     */
    public function setContractFan(\AppBundle\Entity\ContractFan $contractFan = null)
    {
        $this->contractFan = $contractFan;

        return $this;
    }

    /**
     * Get contractFan
     *
     * @return \AppBundle\Entity\ContractFan
     */
    public function getContractFan()
    {
        return $this->contractFan;
    }

    /**
     * Set counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     *
     * @return Ticket
     */
    public function setCounterPart(\AppBundle\Entity\CounterPart $counterPart = null)
    {
        $this->counterPart = $counterPart;

        return $this;
    }

    /**
     * Get counterPart
     *
     * @return \AppBundle\Entity\CounterPart
     */
    public function getCounterPart()
    {
        return $this->counterPart;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Ticket
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }
}
