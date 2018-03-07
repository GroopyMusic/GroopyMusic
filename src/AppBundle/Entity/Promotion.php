<?php

namespace AppBundle\Entity;

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

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function __toString()
    {
        return 'Promotion ' . $this->getMathematicString() . ' du ' . $this->start_date->format('d/m/Y') . ' au ' . $this->end_date->format('d/m/Y');
    }

    public function getMathematicString() {
        switch($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
                return "3 + 1";
                break;
        }
    }

    public function getNbOrganicNeeded() {
        switch($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
                return 3;
            break;
        }
    }

    public function getNbPromotional() {
        switch($this->type) {
            case self::TYPE_THREE_PLUS_ONE:
                return 1;
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
}
