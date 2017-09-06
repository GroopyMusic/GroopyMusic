<?php

// TODO ajouter :
// Photos
// Vidéos
// Musiques qu'ils uploadent
// Site Web
// Lien vers Facebook
// Lien vers twitter

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="artist")
 */
class Artist implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
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
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;

    /**
     * @ORM\OneToMany(targetEntity="Artist_User", mappedBy="artist")
     */
    private $artists_user;

    /**
     * @var Province
     * @ORM\ManyToOne(targetEntity="Province")
     */
    private $province;

    /**
     * @ORM\OneToMany(targetEntity="ArtistOwnershipRequest", mappedBy="artist", cascade={"persist"})
     */
    private $ownership_requests;

    // Form only
    public $ownership_requests_form;

    /**
     * Set artistname
     *
     * @param string $artistname
     *
     * @return UserArtist
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
     * @return UserArtist
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
}
