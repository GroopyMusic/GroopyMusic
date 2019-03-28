<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtist
 *
 * @ORM\Table(name="promotion")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PromotionRepository")
 */

class Promotion
{
    const TYPE_THREE_PLUS_ONE = 'three_plus_one';
    const TYPE_SIX_PLUS_ONE = 'six_plus_one';
    const TYPE_EIGHT_PLUS_TWO = 'eight_plus_two';
    const TYPE_TEN_PLUS_TWO = 'ten_plus_two';
    const TYPE_TWO_TWO_DRINKS = 'two_two_drinks';
    const TYPE_THREE_THREE_DRINKS = 'three_three_drinks';

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function __toString()
    {
        return $this->type != null ?
            'Promotion "' . $this->getMathematicString() . '" du ' . $this->start_date->format('d/m/Y') . ' au ' . $this->end_date->format('d/m/Y')
            :
            'Nouvelle promotion'
        ;
    }

    public function getLeft() {
        switch($this->type) {
            case self::TYPE_TWO_TWO_DRINKS:
                return "2 tickets identiques achetés";
                break;
            case self::TYPE_THREE_PLUS_ONE:
            case self::TYPE_THREE_THREE_DRINKS:
                return "3 tickets identiques achetés";
                break;
            case self::TYPE_SIX_PLUS_ONE:
                return "6 tickets identiques achetés";
                break;
            case self::TYPE_EIGHT_PLUS_TWO:
                return "8 tickets identiques achetés";
            break;
            case self::TYPE_TEN_PLUS_TWO:
                return "10 tickets identiques achetés";
            break;
        }
    }

    public function getRight()
    {
        switch ($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
            case self::TYPE_SIX_PLUS_ONE:
                return "1 ticket du même type offert";
            break;
            case self::TYPE_TEN_PLUS_TWO:
            case self::TYPE_EIGHT_PLUS_TWO:
                return "2 tickets du même type offerts";
            break;
            case self::TYPE_TWO_TWO_DRINKS:
                return "2 tickets boissons offerts";
            break;
            case self::TYPE_THREE_THREE_DRINKS:
                return "3 tickets boissons offerts";
            break;
        }
    }

    public function getMathematicString() {
        switch($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
                return "3 + 1";
            break;
            case self::TYPE_SIX_PLUS_ONE:
                return "6 + 1";
            break;
            case self::TYPE_EIGHT_PLUS_TWO:
                return "8 + 2";
            break;
            case self::TYPE_TEN_PLUS_TWO:
                return "10 + 2";
            break;
            case self::TYPE_TWO_TWO_DRINKS:
                return "+ 2 tickets boissons par lot de 2 tickets achetés";
            break;
            case self::TYPE_THREE_THREE_DRINKS:
                return "+ 3 tickets boissons par lot de 3 tickets achetés";
            break;
        }
    }

    public function getNbOrganicNeeded() {
        switch($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
                return 3;
            break;
            case self::TYPE_SIX_PLUS_ONE:
                return 6;
            break;
            case self::TYPE_EIGHT_PLUS_TWO:
                return 8;
            break;
            case self::TYPE_TEN_PLUS_TWO:
                return 10;
            break;
            default:
                // Other promos don't offer free tickets !
                return PHP_INT_MAX;
            break;
        }
    }

    public function getNbPromotional() {
        switch($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
            case self::TYPE_SIX_PLUS_ONE:
                return 1;
            break;
            case self::TYPE_TEN_PLUS_TWO:
            case self::TYPE_EIGHT_PLUS_TWO:
                return 2;
            break;
            default:
                return 0;
            break;
        }
    }

    public function getNbNeededTopping() {
        switch($this->type) {
            case self::TYPE_TWO_TWO_DRINKS:
                return 2;
            break;
            case self::TYPE_THREE_THREE_DRINKS:
                return 3;
            break;
            default:
                return PHP_INT_MAX;
            break;
        }
    }

    public function getTopping() {
        switch($this->type) {
            case self::TYPE_TWO_TWO_DRINKS:
                return new ToppingString('2 tickets boissons');
            break;
            case self::TYPE_THREE_THREE_DRINKS:
                return new ToppingString('3 tickets boissons');
            break;
            default:
                return null;
            break;
        }
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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(name="start_date", type="date")
     */
    private $start_date;

    /**
     * @ORM\Column(name="end_date", type="date")
     */
    private $end_date;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="BaseContractArtist", cascade={"all"}, mappedBy="promotions")
     */
    protected $contracts;

    /**
     * @var bool
     * @ORM\Column(name="hidden", type="boolean")
     */
    private $hidden;

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
     * Set type
     *
     * @param string $type
     *
     * @return Promotion
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Promotion
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Promotion
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Add contract
     *
     * @param \AppBundle\Entity\BaseContractArtist $contract
     *
     * @return Promotion
     */
    public function addContract(\AppBundle\Entity\BaseContractArtist $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove contract
     *
     * @param \AppBundle\Entity\BaseContractArtist $contract
     */
    public function removeContract(\AppBundle\Entity\BaseContractArtist $contract)
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
     * @return bool
     */
    public function isHidden() {
        return $this->getHidden();
    }

    /**
     * @return bool
     */
    public function getHidden() {
        return $this->hidden;
    }

    /**
     * @param $hidden
     * @return $this
     */
    public function setHidden($hidden) {
        $this->hidden = $hidden;
        return $this;
    }
}
