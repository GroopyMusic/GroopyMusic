<?php

// TODO ajouter champs :
// Specs technique (PDF)
// Délais demandés (string 255)
// Province (entité)
// Prix
// Personne de contact -> ManyToMany !!
// Des photos (array?)

namespace AppBundle\Entity;

use Application\Sonata\MediaBundle\Entity\Gallery;
use Doctrine\Common\Collections\ArrayCollection;
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
        return 'Salle : ' . $this->name;
    }

    public function __construct()
    {
        $base_day = (new \DateTime())->add(new \DateInterval('P'.self::MINIMUM_DAYS_FROM_TODAY_FOR_CROWDFUNDING.'D'))->format(self::DATE_FORMAT);

        $this->available_dates = [];
        $this->cron_automatic_days = array(
            'days_0' => false,
            'days_1' => false,
            'days_2' => false,
            'days_3' => false,
            'days_4' => false,
            'days_5' => false,
            'days_6' => false
        );
        $this->cron_explored_dates = array(
            'days_0' => $base_day,
            'days_1' => $base_day,
            'days_2' => $base_day,
            'days_3' => $base_day,
            'days_4' => $base_day,
            'days_5' => $base_day,
            'days_6' => $base_day,
        );
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
            if($this->cron_automatic_days[$i]) {
                while($current_cursor != $max_cursor) {
                    $current_date = new \DateTime($current_cursor);
                    // It must be the right day of the week & not yet in the array to be added
                    if(intval($current_date->format('w')) == array_search($i, array_keys($this->cron_automatic_days)) && !in_array($current_cursor, $this->available_dates)) {
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
     * @ORM\Column(name="available_dates", type="array", nullable=true)
     */
    private $available_dates;

    /**
     * @var string
     */
    private $available_dates_string;

    /**
     * @var array
     * @ORM\Column(name="cron_explored_dates", type="array")
     */
    private $cron_explored_dates;

    /**
     * @var array
     * @ORM\Column(name="cron_automatic_days", type="array")
     */
    private $cron_automatic_days;

    /**
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2)
     */
    private $price;

    /**
     * @var Gallery
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="gallery", referencedColumnName="id")
     * })
     */
    private $gallery;

    /**
     * @ORM\Column(name="delay", type="smallint")
     */
    private $delay;

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
        foreach($cronAutomaticDays as $i => $day) {
            if($day) {
                $this->cron_automatic_days[$i] = $day;
            }
            else {
                $this->cron_automatic_days[$i] = false;
            }
        }

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

    /**
     * @return string
     */
    public function getAvailableDatesString()
    {
        return $this->available_dates_string = implode(',', $this->available_dates);
    }

    /**
     * @param string $available_dates_string
     */
    public function setAvailableDatesString($available_dates_string)
    {
        $this->available_dates_string = $available_dates_string;
        $this->available_dates = explode(',', $available_dates_string);
    }


    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return Hall
     */
    public function setProvince(\AppBundle\Entity\Province $province = null)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return \AppBundle\Entity\Province
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Hall
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Hall
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set gallery
     *
     * @param \Application\Sonata\MediaBundle\Entity\Gallery $gallery
     *
     * @return Hall
     */
    public function setGallery(\Application\Sonata\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery
     *
     * @return \Application\Sonata\MediaBundle\Entity\Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set delay
     *
     * @param integer $delay
     *
     * @return Hall
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
     * Add contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     *
     * @return Hall
     */
    public function addContactPerson(\AppBundle\Entity\ContactPerson $contactPerson)
    {
        $this->contact_person[] = $contactPerson;

        return $this;
    }

    /**
     * Remove contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     */
    public function removeContactPerson(\AppBundle\Entity\ContactPerson $contactPerson)
    {
        $this->contact_person->removeElement($contactPerson);
    }
}
