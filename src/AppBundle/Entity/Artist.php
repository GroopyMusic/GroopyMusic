<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistRepository")
 * @ORM\Table(name="artist")
 **/
 // @UniqueEntity("artistname", repositoryMethod="findNotDeleted")
class Artist implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\Sluggable\Sluggable;

    const PHOTOS_DIR = 'uploads/artist_gallery/';

    public static function getWebPath(Photo $photo) {
        return self::PHOTOS_DIR . $photo->getFilename();
    }

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch(\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
    }

    public function getDefaultLocale() {
        return 'fr';
    }

    public function __toString()
    {
        return '' . $this->artistname;
    }

    public function __construct(Phase $phase = null)
    {
        $this->phase = $phase;
        $this->deleted = false;
        $this->genres = new ArrayCollection();
        $this->artists_user = new ArrayCollection();
        $this->ownership_requests = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->date_creation = new \DateTime();
        $this->accept_conditions = false;
        $this->visible = false;
        $this->validated = false;
        $this->information_session = null;
    }

    public function getSluggableFields() {
        return ['artistname'];
    }

    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);
        return $this;
    }

    public function getLocale()
    {
        return $this->getCurrentLocale();
    }

    public function getSafename() {
        return $this->slug;
    }

    public function isActive() {
        return !$this->deleted;
    }

    public function getOwners() {
        return array_map(function($elem) {
                return $elem->getUser();
        }, $this->artists_user->toArray());
    }

    public function getScore(User $user) {
        if(mt_rand(1,2) == 1) { return 0; }

        $score = 0;
        foreach($user->getGenres() as $genre) {
            if($this->genres->contains($genre)) {
                if(mt_rand(1,3) > 1) {
                   $score++;
                }
            }
        }
        return $score;
    }

    public function canBeLeft() {
        $currentContract = $this->currentContract();
        return $currentContract == null;
    }

    public function isAvailable() {
        $currentContract = $this->currentContract(true);
        return $currentContract == null || $currentContract->isInSuccessfulState();
    }

    public function hasCurrentContract() {
        return $this->currentContract() != null;
    }

    public function hasOneLink() {
        return !empty($this->website)
            || !empty($this->twitter)
            || !empty($this->facebook)
            || !empty($this->spotify)
            || !empty($this->soundcloud)
            || !empty($this->bandcamp)
            || !empty($this->instagram)
        ;
    }

    public function getAllPhotos() {
        $photos = array();

        if($this->profilepic != null) {
            $photos[] = $this->profilepic;
        }
        foreach($this->photos as $p) {
            $photos[] = $p;
        }

        return $photos;
    }

    // Unmapped
    private $currentContracts = false;

    public function currentContract($allow_preval = false) {
        if($this->currentContracts === false) {
            $this->currentContracts = [];

            foreach($this->getPerformances() as $performance) {
                $festivalDay = $performance->getFestivalDay();
                $contract = $festivalDay->getFestival();
                if($festivalDay->getDate()  >= (new \DateTime()) && !$contract->getFailed()) {
                    $this->currentContracts[] = $contract;
                }
            }
            $this->currentContracts = array_unique($this->currentContracts);
        }
        return $this->currentContracts;
    }

    public function currentContracts() {
        return $this->currentContract();
    }

    public function getPassedSuccessfulContracts() {
        $contracts = [];

        foreach($this->contracts as $contract) {
            /** @var ContractArtist $contract */
            if($contract->getLastFestivalDate() < (new \DateTime()) && $contract->getSuccessful() && !$contract->getFailed()) {
                $contracts[] = $contract;
            }
        }

        return $contracts;
    }

    public function getInformationSessionWithDate() {
        if($this->information_session)
            return $this->information_session->getNameWithDate();
        return '';
    }

    // Form only

    private $accept_conditions;
    public function getAcceptConditions() {return $this->accept_conditions;}
    public function setAcceptConditions($ac) { $this->accept_conditions = $ac; return $this;}

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="artistname", type="string", length=67)
     */
    private $artistname;

    /**
     * @ORM\ManyToOne(targetEntity="Phase")
     * @ORM\JoinColumn(nullable=true)
     */
    private $phase;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Artist_User", mappedBy="artist")
     */
    private $artists_user;

    /**
     * @var Province
     * @ORM\ManyToOne(targetEntity="Province")
     */
    private $province;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ArtistOwnershipRequest", mappedBy="artist", cascade={"persist"})
     */
    private $ownership_requests;

    /**
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * @var Photo
     *
     * @ORM\OneToOne(targetEntity="Photo", cascade={"all"})
     */
    private $profilepic;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Photo", cascade={"all"})
     */
    private $photos;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Video", cascade={"all"})
     */
    private $videos;

    /**
     * @var string
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     * @ORM\Column(name="facebook", type="string", length=255, nullable=true)
     */
    private $facebook;

    /**
     * @var string
     * @ORM\Column(name="twitter", type="string", length=255, nullable=true)
     */
    private $twitter;

    /**
     * @var string
     * @ORM\Column(name="spotify", type="string", length=255, nullable=true)
     */
    private $spotify;

    /**
     * @var string
     * @ORM\Column(name="soundcloud", type="string", length=255, nullable=true)
     */
    private $soundcloud;

    /**
     * @var string
     * @ORM\Column(name="bandcamp", type="string", length=255, nullable=true)
     */
    private $bandcamp;

    /**
     * @var string
     * @ORM\Column(name="instagram", type="string", length=255, nullable=true)
     */
    private $instagram;

    /**
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\OneToMany(targetEntity="BaseContractArtist", mappedBy="artist")
     */
    private $base_contracts;

    /**
     * @ORM\OneToMany(targetEntity="ContractArtist", mappedBy="main_artist")
     */
    private $contracts;

    /**
     * @var bool
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

    /**
     * @var bool
     * @ORM\Column(name="validated", type="boolean")
     */
    private $validated;

    /**
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="ArtistPerformance", mappedBy="artist")
     */
    private $performances;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\InformationSession", inversedBy="artists")
     * @ORM\JoinColumn(nullable=true)
     */
    private $information_session;

    // Form only
    /** @var ArrayCollection */
    public $ownership_requests_form;

    /**
     * Set artistname
     *
     * @param string $artistname
     *
     * @return Artist
     */
    public function setArtistname($artistname)
    {
        $this->artistname = $artistname;

        return $this;
    }

    /**
     * Get artistname
     *
     * @return string
     */
    public function getArtistname()
    {
        return $this->artistname;
    }

    /**
     * Set phase
     *
     * @param \AppBundle\Entity\Phase $phase
     *
     * @return Artist
     */
    public function setPhase(\AppBundle\Entity\Phase $phase)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Get phase
     *
     * @return \AppBundle\Entity\Phase
     */
    public function getPhase()
    {
        return $this->phase;
    }

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
     * Add genre
     *
     * @param \AppBundle\Entity\Genre $genre
     *
     * @return Artist
     */
    public function addGenre(\AppBundle\Entity\Genre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    /**
     * Remove genre
     *
     * @param \AppBundle\Entity\Genre $genre
     */
    public function removeGenre(\AppBundle\Entity\Genre $genre)
    {
        $this->genres->removeElement($genre);
    }

    /**
     * Get genres
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * Add artistsUser
     *
     * @param \AppBundle\Entity\Artist_User $artistsUser
     *
     * @return Artist
     */
    public function addArtistsUser(\AppBundle\Entity\Artist_User $artistsUser)
    {
        $this->artists_user[] = $artistsUser;

        return $this;
    }

    /**
     * Remove artistsUser
     *
     * @param \AppBundle\Entity\Artist_User $artistsUser
     */
    public function removeArtistsUser(\AppBundle\Entity\Artist_User $artistsUser)
    {
        $this->artists_user->removeElement($artistsUser);
    }

    /**
     * Get artistsUser
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArtistsUser()
    {
        return $this->artists_user;
    }

    /**
     * Add ownershipRequest
     *
     * @param \AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest
     *
     * @return Artist
     */
    public function addOwnershipRequest(\AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest)
    {
        $this->ownership_requests[] = $ownershipRequest;
        $ownershipRequest->setArtist($this);

        return $this;
    }

    /**
     * Remove ownershipRequest
     *
     * @param \AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest
     */
    public function removeOwnershipRequest(\AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest)
    {
        $this->ownership_requests->removeElement($ownershipRequest);
    }

    /**
     * Get ownershipRequests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnershipRequests()
    {
        return $this->ownership_requests;
    }

    /**
     * Remove ownershipRequest
     *
     * @param \AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest
     */
    public function removeOwnershipRequestForm(\AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest)
    {
        $this->ownership_requests_form->removeElement($ownershipRequest);
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return Artist
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
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Artist
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        if($deleted) {
            $this->setVisible(false);
        }

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Artist
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return Artist
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return Artist
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set spotify
     *
     * @param string $spotify
     *
     * @return Artist
     */
    public function setSpotify($spotify)
    {
        $this->spotify = $spotify;

        return $this;
    }

    /**
     * Get spotify
     *
     * @return string
     */
    public function getSpotify()
    {
        return $this->spotify;
    }

    /**
     * Add photo
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return Artist
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
     * Add video
     *
     * @param \AppBundle\Entity\Video $video
     *
     * @return Artist
     */
    public function addVideo(\AppBundle\Entity\Video $video)
    {
        $this->videos[] = $video;

        return $this;
    }

    /**
     * Remove video
     *
     * @param \AppBundle\Entity\Video $video
     */
    public function removeVideo(\AppBundle\Entity\Video $video)
    {
        $this->videos->removeElement($video);
    }

    /**
     * Set profilepic
     *
     * @param \AppBundle\Entity\Photo $profilepic
     *
     * @return Artist
     */
    public function setProfilepic(\AppBundle\Entity\Photo $profilepic = null)
    {
        $this->profilepic = $profilepic;

        return $this;
    }

    /**
     * Get profilepic
     *
     * @return \AppBundle\Entity\Photo
     */
    public function getProfilepic()
    {
        return $this->profilepic;
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
     * Get videos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Artist
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

    /**
     * Add contract
     *
     * @param \AppBundle\Entity\BaseContractArtist $contract
     *
     * @return Artist
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
     * Set soundcloud
     *
     * @param string $soundcloud
     *
     * @return Artist
     */
    public function setSoundcloud($soundcloud)
    {
        $this->soundcloud = $soundcloud;

        return $this;
    }

    /**
     * Get soundcloud
     *
     * @return string
     */
    public function getSoundcloud()
    {
        return $this->soundcloud;
    }

    /**
     * Set bandcamp
     *
     * @param string $bandcamp
     *
     * @return Artist
     */
    public function setBandcamp($bandcamp)
    {
        $this->bandcamp = $bandcamp;

        return $this;
    }

    /**
     * Get bandcamp
     *
     * @return string
     */
    public function getBandcamp()
    {
        return $this->bandcamp;
    }

    /**
     * Add baseContract
     *
     * @param \AppBundle\Entity\BaseContractArtist $baseContract
     *
     * @return Artist
     */
    public function addBaseContract(\AppBundle\Entity\BaseContractArtist $baseContract)
    {
        $this->base_contracts[] = $baseContract;

        return $this;
    }

    /**
     * Remove baseContract
     *
     * @param \AppBundle\Entity\BaseContractArtist $baseContract
     */
    public function removeBaseContract(\AppBundle\Entity\BaseContractArtist $baseContract)
    {
        $this->base_contracts->removeElement($baseContract);
    }

    /**
     * Get baseContracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBaseContracts()
    {
        return $this->base_contracts;
    }

    /**
     * Set instagram
     *
     * @param string $instagram
     *
     * @return Artist
     */
    public function setInstagram($instagram)
    {
        $this->instagram = $instagram;

        return $this;
    }

    /**
     * Get instagram
     *
     * @return string
     */
    public function getInstagram()
    {
        return $this->instagram;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     *
     * @return Artist
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
     * Set validated
     *
     * @param boolean $validated
     *
     * @return Artist
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get validated
     *
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Artist
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Remove ownershipRequestsForm
     *
     * @param \AppBundle\Entity\ArtistOwnershipRequest $ownershipRequestsForm
     */
    public function removeOwnershipRequestsForm(\AppBundle\Entity\ArtistOwnershipRequest $ownershipRequestsForm)
    {
        $this->ownership_requests_form->removeElement($ownershipRequestsForm);
    }

    /**
     * Get ownershipRequestsForm
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnershipRequestsForm()
    {
        return $this->ownership_requests_form;
    }

    /**
     * Add performance
     *
     * @param \AppBundle\Entity\ArtistPerformance $performance
     *
     * @return Artist
     */
    public function addPerformance(\AppBundle\Entity\ArtistPerformance $performance)
    {
        $this->performances[] = $performance;

        return $this;
    }

    /**
     * Remove performance
     *
     * @param \AppBundle\Entity\ArtistPerformance $performance
     */
    public function removePerformance(\AppBundle\Entity\ArtistPerformance $performance)
    {
        $this->performances->removeElement($performance);
    }

    /**
     * Get performances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPerformances()
    {
        return $this->performances;
    }

    /**
     * @return mixed
     */
    public function getInformationSession()
    {
        return $this->information_session;
    }

    /**
     * @param mixed $information_session
     */
    public function setInformationSession($information_session)
    {
        $this->information_session = $information_session;
    }


}
