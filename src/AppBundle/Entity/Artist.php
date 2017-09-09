<?php

namespace AppBundle\Entity;

use Application\Sonata\MediaBundle\Entity\Gallery;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistRepository")
 * @ORM\Table(name="artist")
 */
class Artist implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

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

    public function __construct(Phase $phase)
    {
        $this->phase = $phase;
        $this->deleted = false;
        $this->genres = new ArrayCollection();
        $this->artists_user = new ArrayCollection();
        $this->ownership_requests = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->videos = new ArrayCollection();
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
        return urlencode($this->artistname);
    }

    public function isActive() {
        return !$this->deleted;
    }

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
     * @ORM\JoinColumn(nullable=false)
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


    // Form only
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
     * Add ownershipRequest
     *
     * @param \AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest
     *
     * @return Artist
     */
    public function addOwnershipRequestForm(\AppBundle\Entity\ArtistOwnershipRequest $ownershipRequest)
    {
        $this->ownership_requests_form[] = $ownershipRequest;
        $ownershipRequest->setArtist($this);

        return $this;
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
}
