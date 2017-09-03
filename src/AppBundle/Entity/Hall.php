<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hall
 *
 * @ORM\Table(name="hall")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HallRepository")
 */
class Hall extends Partner
{
    const MINIMUM_DAYS_FROM_TODAY_FOR_CROWDFUNDING = 90;
    const DAYS_MARGIN_FOR_CROWDFUNDING = 10;
    const DATE_FORMAT = 'm/d/Y';

    public function __toString()
    {
        return $this->name;
    }

    public function __construct()
    {
        $yesterday = (new \DateTime('yesterday'))->format(self::DATE_FORMAT);
        $this->available_dates = array();
        $this->cron_automatic_days = array();
        $this->cron_explored_dates = array($yesterday, $yesterday, $yesterday, $yesterday, $yesterday, $yesterday, $yesterday);
    }

    public function refreshDates() {

        // Clean passed dates
        foreach($this->available_dates as $i => $ad) {
            if((new \DateTime($ad)) < (new \DateTime('now'))) {
                unset($this->available_dates[$i]);
            }
        }

        $max_cursor = (new \DateTime())->add(new \DateInterval('P'.(self::MINIMUM_DAYS_FROM_TODAY_FOR_CROWDFUNDING + self::DAYS_MARGIN_FOR_CROWDFUNDING).'D'))->format(self::DATE_FORMAT);
        $default_cursor = (new \DateTime())->add(new \DateInterval('P'.self::MINIMUM_DAYS_FROM_TODAY_FOR_CROWDFUNDING.'D'))->format(self::DATE_FORMAT);

        // For each possible day
        foreach($this->cron_explored_dates as $i => $current_cursor) {
            // There is a rule -> refresh dates & update cursor
            if(in_array($i, $this->cron_automatic_days)) {
                while($current_cursor != $max_cursor) {
                    $current_date = new \DateTime($current_cursor);
                    // It must be the right day of the week & not yet in the array to be added
                    if(intval($current_date->format('w')) == $i && !in_array($current_cursor, $this->available_dates)) {
                        $this->available_dates[] = $current_cursor;
                    }
                    $current_cursor = $current_date->add(new \DateInterval('P1D'))->format(self::DATE_FORMAT);
                }
                $this->cron_explored_dates[$i] = $current_cursor;
            }
            // No rule -> just set the cursor right
            else {
                $this->cron_explored_dates[$i] = $default_cursor;
            }
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
     * @var int
     *
     * @ORM\Column(name="capacity", type="smallint")
     */
    private $capacity;

    /**
     * @ORM\ManyToOne(targetEntity="Step", inversedBy="halls")
     */
    private $step;

    /**
     * @var array
     * @ORM\Column(name="available_dates", type="simple_array")
     */
    private $available_dates;

    /**
     * @var array
     * @ORM\Column(name="cron_explored_dates", type="simple_array")
     */
    private $cron_explored_dates;

    /**
     * @var array
     * @ORM\Column(name="cron_automatic_days", type="simple_array")
     */
    private $cron_automatic_days;

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
     * Set capacity
     *
     * @param integer $capacity
     *
     * @return Hall
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return Hall
     */
    public function setStep(\AppBundle\Entity\Step $step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \AppBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set availableDates
     *
     * @param array $availableDates
     *
     * @return Hall
     */
    public function setAvailableDates($availableDates)
    {
        $this->available_dates = $availableDates;

        return $this;
    }

    /**
     * Get availableDates
     *
     * @return array
     */
    public function getAvailableDates()
    {
        return $this->available_dates;
    }

    /**
     * Set cronExploredDates
     *
     * @param array $cronExploredDates
     *
     * @return Hall
     */
    public function setCronExploredDates($cronExploredDates)
    {
        $this->cron_explored_dates = $cronExploredDates;

        return $this;
    }

    /**
     * Get cronExploredDates
     *
     * @return array
     */
    public function getCronExploredDates()
    {
        return $this->cron_explored_dates;
    }

    /**
     * Set cronAutomaticDays
     *
     * @param array $cronAutomaticDays
     *
     * @return Hall
     */
    public function setCronAutomaticDays($cronAutomaticDays)
    {
        $this->cron_automatic_days = $cronAutomaticDays;

        return $this;
    }

    /**
     * Get cronAutomaticDays
     *
     * @return array
     */
    public function getCronAutomaticDays()
    {
        return $this->cron_automatic_days;
    }
}
