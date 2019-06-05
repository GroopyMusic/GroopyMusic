<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\CounterPart;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="YBSubEvent")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\YBSubEventRepository")
 */
class YBSubEvent {

    public function __construct()
    {
        $this->counterparts = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getDate()->format('d/m/Y');
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
     * @var YBContractArtist
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\YBContractArtist", inversedBy="sub_events")
     */
    private $campaign;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CounterPart", mappedBy="sub_events")
     */
    private $counterparts;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


    public function getId() {
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
     * @param mixed $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return ArrayCollection
     */
    public function getCounterparts()
    {
        return $this->counterparts;
    }

    /**
     * @param ArrayCollection $counterparts
     */
    public function setCounterparts(ArrayCollection $counterparts)
    {
        $this->counterparts = $counterparts;
    }

    /**
     * @param CounterPart $counterPart
     * @return $this
     */
    public function addCounterpart(CounterPart $counterPart) {
        if($this->counterparts == null) {
            $this->counterparts = new ArrayCollection();
        }
        $this->counterparts->add($counterPart);

        return $this;
    }

    public function removeCounterpart(CounterPart $counterPart) {
        $this->counterparts->remove($counterPart);
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }
}