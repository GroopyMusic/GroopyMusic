<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Hall
 *
 * @ORM\Table(name="hall")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HallRepository")
 *
 * @Vich\Uploadable
 */
class Hall extends Partner
{
    private $dummyForm;
    public function setDummyForm($dummyForm){$this->dummyForm = $dummyForm;}
    public function getDummyForm() {return $this->dummyForm;}

    const DATE_FORMAT = 'm/d/Y';

    const PHOTOS_DIR = 'uploads/hall_gallery/';
    const FILES_DIR = 'uploads/hall_technicalspecs/';

    public static function getWebPath(Photo $photo) {
        return self::PHOTOS_DIR . $photo->getFilename();
    }

    public function getUploadFileName() {
        return $this->getTechnicalSpecsFileName();
    }

    public function getTechnicalSpecsFileName() {
        try {
            return $this->slug . '-' . uniqid();
        } catch(Exception $e) {
            return uniqid();
        }
    }

    // Needs to be called before using the 2 next methods
    // as this sorts the available dates
    public function hasClearDates() {
        usort($this->available_dates, array($this, "cmp_dates"));
        return count($this->available_dates) >= 2;
    }

    public function getFirstDate() {
        return new \DateTime($this->available_dates[0]);
    }

    public function getLastDate() {
        return new \DateTime($this->available_dates[count($this->available_dates) - 1]);
    }

    public function getSafename() {
        return urlencode($this->getName());
    }

    public function __toString() {
        return 'Salle : ' . $this->getName();
    }

    private function cmp_dates($date1, $date2) {
        if($date1 == $date2)
            return 0;

        if($this->max_date($date1, $date2) == $date1)
            return 1;

        return -1;
    }

    private function max_date($date1, $date2) {
        $date1_elems = explode('/', $date1);
        $date2_elems = explode('/', $date2);

        if(intval($date1_elems[2]) > intval($date2_elems[2])) {
            return $date1;
        }
        if(intval($date1_elems[2]) < intval($date2_elems[2])) {
            return $date2;
        }
        if(intval($date1_elems[0]) > intval($date2_elems[0])) {
            return $date1;
        }
        if(intval($date1_elems[0]) < intval($date2_elems[0])) {
            return $date2;
        }
        if(intval($date1_elems[1]) > intval($date2_elems[1])) {
            return $date1;
        }
        if(intval($date1_elems[1]) < intval($date2_elems[1])) {
            return $date2;
        }
        return $date1;
    }

    public function getCronAutomaticDaysFormatted() {
        // TODO export this logic to use the translations in xliff
        $translations = array(
            'days_0' => 'Dimanche', 'days_1' => 'Lundi', 'days_2' => 'Mardi', 'days_3' => 'Mercredi', 'days_4' => 'Jeudi', 'days_5' => 'Vendredi', 'days_6' => 'Samedi'
        );

        $string = '';
        foreach($this->cron_automatic_days as $i => $day) {
            if($day)
                $string .= $translations[$i] . ' ';
        }
        return $string;
    }

    public function __construct()
    {
        parent::__construct();

        $base_day = (new \DateTime())->format(self::DATE_FORMAT);

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

        $this->updatedAt = new \DateTime();
    }

    public function refreshDates() {

        $minimum_days_from_today_for_crowdfunding = $this->getStep()->getDeadlineDuration() + $this->getStep()->getDelay();
        $days_margin_for_crowdfunding = $this->getStep()->getDelayMargin();

        $max_cursor = (new \DateTime())->add(new \DateInterval('P'.($minimum_days_from_today_for_crowdfunding + $days_margin_for_crowdfunding).'D'))->format(self::DATE_FORMAT);
        $default_cursor = (new \DateTime())->add(new \DateInterval('P'.$minimum_days_from_today_for_crowdfunding.'D'))->format(self::DATE_FORMAT);

        // Clean "out of scope" dates
        $this->available_dates = array_filter($this->available_dates, function($date) use ($default_cursor) {
            return (new \DateTime($date)) >= (new \DateTime($default_cursor));
        });

        foreach($this->available_dates as $i => $ad) {

        }

        // For each possible day
        foreach($this->cron_explored_dates as $i => $current_cursor) {
            $current_cursor = $this->max_date($default_cursor, $current_cursor);

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
                $this->cron_explored_dates[$i] = $current_cursor;
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
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Photo", cascade={"all"})
     */
    private $photos;

    /**
     * @ORM\Column(name="delay", type="smallint")
     */
    private $delay;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="hall_technical_specs", fileNameProperty="technical_specs_name")
     *
     * @var File
     */
    private $technical_specs_file;

    /**
     * @ORM\Column(name="technical_specs_name", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $technical_specs_name;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Hall
     */
    public function setTechnicalSpecsFile(File $file = null)
    {
        $this->technical_specs_file = $file;

        if ($file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getTechnicalSpecsFile()
    {
        return $this->technical_specs_file;
    }

    /**
     * @param string $imageName
     *
     * @return Hall
     */
    public function setTechnicalSpecsName($technicalSpecsName)
    {
        $this->technical_specs_name = $technicalSpecsName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTechnicalSpecsName()
    {
        return $this->technical_specs_name;
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
     * Add photo
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return Hall
     */
    public function addPhoto(\AppBundle\Entity\Photo $photo)
    {
        $this->photos[] = $photo;

        return $this;
    }

    /**
     * Remove photo
     *
     * @param \AppBundle\Entity\Photo $photo
     */
    public function removePhoto(\AppBundle\Entity\Photo $photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Hall
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     *
     * @return Hall
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set ephemeral
     *
     * @param boolean $ephemeral
     *
     * @return Hall
     */
    public function setEphemeral($ephemeral)
    {
        $this->ephemeral = $ephemeral;

        return $this;
    }

    /**
     * Get ephemeral
     *
     * @return boolean
     */
    public function getEphemeral()
    {
        return $this->ephemeral;
    }
}
