<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="step")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StepRepository")
 */
class Step extends BaseStep
{
    public function __construct()
    {
        parent::__construct();
        $this->type = parent::TYPE_CONCERT;
        $this->delay = 60;
        $this->delay_margin = 30;
        $this->deadline_duration = 30;
    }

    public function getAvailableDates(Province $province = null) {
        $dates = array();

        foreach($this->getHalls() as $hall) {
            if($hall->getVisible() && ($province == null || $province == $hall->getProvince()))
                $dates = array_merge($dates, $hall->getAvailableDates());
        }

        return array_values($dates);
    }

    public function getAvailableDatesFormatted(Province $province = null) {
        $availableDates = $this->getAvailableDates($province);

        $display = '';
        $count = count($availableDates);
        for($i = 0; $i < $count; $i++) {
            $display .= $availableDates[$i];
            if($i != $count - 1) {
                $display .= ',';
            }
        }
        return $display ;
    }

    /**
     * @ORM\OneToMany(targetEntity="Hall", mappedBy="step")
     */
    private $halls;

    /**
     * @ORM\Column(name="approximate_capacity", type="smallint")
     */
    private $approximate_capacity;

    /**
     * @ORM\Column(name="delay", type="smallint")
     */
    private $delay;

    /**
     * @ORM\Column(name="delay_margin", type="smallint")
     */
    private $delay_margin;

    /**
     * @ORM\Column(name="min_tickets", type="smallint")
     */
    private $min_tickets;

    /**
     * @ORM\Column(name="max_tickets", type="smallint")
     */
    private $max_tickets;


    /**
     * Set approximateCapacity
     *
     * @param integer $approximateCapacity
     *
     * @return Step
     */
    public function setApproximateCapacity($approximateCapacity)
    {
        $this->approximate_capacity = $approximateCapacity;

        return $this;
    }

    /**
     * Get approximateCapacity
     *
     * @return integer
     */
    public function getApproximateCapacity()
    {
        return $this->approximate_capacity;
    }

    /**
     * Set delay
     *
     * @param integer $delay
     *
     * @return Step
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Get delay
     *
     * @return integer
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Set delayMargin
     *
     * @param integer $delayMargin
     *
     * @return Step
     */
    public function setDelayMargin($delayMargin)
    {
        $this->delay_margin = $delayMargin;

        return $this;
    }

    /**
     * Get delayMargin
     *
     * @return integer
     */
    public function getDelayMargin()
    {
        return $this->delay_margin;
    }

    /**
     * Set minTickets
     *
     * @param integer $minTickets
     *
     * @return Step
     */
    public function setMinTickets($minTickets)
    {
        $this->min_tickets = $minTickets;

        return $this;
    }

    /**
     * Get minTickets
     *
     * @return integer
     */
    public function getMinTickets()
    {
        return $this->min_tickets;
    }

    /**
     * Set maxTickets
     *
     * @param integer $maxTickets
     *
     * @return Step
     */
    public function setMaxTickets($maxTickets)
    {
        $this->max_tickets = $maxTickets;

        return $this;
    }

    /**
     * Get maxTickets
     *
     * @return integer
     */
    public function getMaxTickets()
    {
        return $this->max_tickets;
    }

    /**
     * Add hall
     *
     * @param \AppBundle\Entity\Hall $hall
     *
     * @return Step
     */
    public function addHall(\AppBundle\Entity\Hall $hall)
    {
        $this->halls[] = $hall;

        return $this;
    }

    /**
     * Remove hall
     *
     * @param \AppBundle\Entity\Hall $hall
     */
    public function removeHall(\AppBundle\Entity\Hall $hall)
    {
        $this->halls->removeElement($hall);
    }

    /**
     * Get halls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHalls()
    {
        return $this->halls;
    }
}
