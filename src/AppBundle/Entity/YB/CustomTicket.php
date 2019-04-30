<?php

namespace AppBundle\Entity\YB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CustomTicket
 * @package AppBundle\Entity\YB
 * @ORM\Table(name="yb_custom_tickets")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\CustomTicketRepository")
 */
class CustomTicket {

    public function __construct(YBContractArtist $campaign){
        $this->campaign = $campaign;
        $this->stations = new ArrayCollection();
    }

    public function getGMapsUrlToDisplay($maps_key, $maps_secret){
        $mapsAdress = str_replace(' ', '+', $this->campaign->getVenue()->getAddress()->getNatural());
        $url = 'https://maps.googleapis.com/maps/api/staticmap?center=';
        $url .= $mapsAdress;
        $url .= '&zoom=13&size=600x300&maptype=roadmap';
        foreach ($this->stations as $station){
            $marker = '&'.urlencode($this->getMarkerString($station));
            $url .= $marker;
        }
        $url = $url.'&key='.$maps_key;
        $googleMapsUrl = $this->signUrl($url, $maps_secret);
        return $googleMapsUrl;
    }

    private function signUrl($my_url, $maps_secret){
        $url = parse_url($my_url);
        $privatekey = $maps_secret;
        $urlToSign =  $url['path'] . "?" . $url['query'];
        $decodedKey = $this->decodeBase64UrlSafe($privatekey);
        $signature = hash_hmac("sha1", $urlToSign, $decodedKey, true);
        $encodedSignature = $this->encodeBase64UrlSafe($signature);
        $originalUrl = $url['scheme'] . "://" . $url['host'] . $url['path'] . "?" . $url['query'];
        $finalUrl = $originalUrl.'&signature='.$encodedSignature;
        $finalUrl = str_replace('\u0026', '&', $finalUrl);
        $finalUrl = str_replace('\\', '', $finalUrl);
        return $finalUrl;
    }

    function encodeBase64UrlSafe($value){
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($value));
    }

    function decodeBase64UrlSafe($value){
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $value));
    }

    private function getMarkerString(PublicTransportStation $station){
        $string = 'markers=';
        switch ($station->getType()){
            case 'SNCB' :
                $string .= 'color:blue%7C'; break;
            case 'STIB' :
                $string .= 'color:green%7C'; break;
            default:
                $string .= 'color:black%7C'; break;
        }
        $string .= 'label:'.$station->getName().'%7C';
        $string .= ''.$station->getLatitude().','.$station->getLongitude();
        return $string;
    }

    public function getMapQuestUrl($key){
        $mapsAdress = str_replace(' ', '+', $this->campaign->getVenue()->getAddress()->getNatural());
        $base_url = 'https://www.mapquestapi.com/staticmap/v5/map?locations=';
        $base_url = $base_url . $mapsAdress . '|marker-red';
        for ($i = 0; $i < count($this->stations); $i++){
            $color = $this->getColorFromType($this->stations[$i]->getType());
            $base_url = $base_url . '||' . $this->stations[$i]->getLatitude() . ',' . $this->stations[$i]->getLongitude() .
                '|marker-' . ($i + 1) . '-' . $color;
        }
        $url = $base_url . '&size=210,200&key=' . $key;
        return $url;
    }

    private function getColorFromType($type){
        switch ($type){
            case 'SNCB' : return 'blue';
            case 'STIB' : return 'green';
            default : return 'black';
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
     * @var YBContractArtist
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\YB\YBContractArtist", cascade={"all"}, inversedBy="customTicket")
     * @ORM\JoinColumn(nullable=true)
     */
    private $campaign;

    /**
     * @var boolean
     *
     * @ORM\Column(name="image_added", type="boolean")
     */
    private $imageAdded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="venue_map_added", type="boolean")
     */
    private $venueMapAdded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="commute_text_added", type="boolean")
     */
    private $publicTransportTextInfosAdded;

    /**
     * @var string
     *
     * @ORM\Column(name="commute_text", type="string", length=300, nullable=true)
     */
    private $publicTransportTextInfos;

    /**
     * @var boolean
     *
     * @ORM\Column(name="custom_text_added", type="boolean")
     */
    private $customInfosAdded;

    /**
     * @var string
     *
     * @ORM\Column(name="custom_text", type="string", length=300, nullable=true)
     */
    private $customInfos;

    /**
     * @var boolean
     *
     * @ORM\Column(name="commute_added", type="boolean")
     */
    private $commuteAdded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sncb_infos_added", type="boolean")
     */
    private $commuteSNCBAdded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="stib_infos_added", type="boolean")
     */
    private $commuteSTIBAdded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tec_infos_added", type="boolean")
     */
    private $commuteTECAdded;

    /**
     * @var
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\YB\PublicTransportStation", cascade={"persist"})
     */
    private $stations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $mapsImagePath;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return YBContractArtist
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param YBContractArtist $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return boolean
     */
    public function isImageAdded()
    {
        return $this->imageAdded;
    }

    /**
     * @param boolean $imageAdded
     */
    public function setImageAdded($imageAdded)
    {
        $this->imageAdded = $imageAdded;
    }

    /**
     * @return boolean
     */
    public function isVenueMapAdded()
    {
        return $this->venueMapAdded;
    }

    /**
     * @param boolean $venueMapAdded
     */
    public function setVenueMapAdded($venueMapAdded)
    {
        $this->venueMapAdded = $venueMapAdded;
    }

    /**
     * @return boolean
     */
    public function isPublicTransportTextInfosAdded()
    {
        return $this->publicTransportTextInfosAdded;
    }

    /**
     * @param boolean $publicTransportTextInfosAdded
     */
    public function setPublicTransportTextInfosAdded($publicTransportTextInfosAdded)
    {
        $this->publicTransportTextInfosAdded = $publicTransportTextInfosAdded;
    }

    /**
     * @return string
     */
    public function getPublicTransportTextInfos()
    {
        return $this->publicTransportTextInfos;
    }

    /**
     * @param string $publicTransportTextInfos
     */
    public function setPublicTransportTextInfos($publicTransportTextInfos)
    {
        $this->publicTransportTextInfos = $publicTransportTextInfos;
    }

    /**
     * @return boolean
     */
    public function isCustomInfosAdded()
    {
        return $this->customInfosAdded;
    }

    /**
     * @param boolean $customInfosAdded
     */
    public function setCustomInfosAdded($customInfosAdded)
    {
        $this->customInfosAdded = $customInfosAdded;
    }

    /**
     * @return string
     */
    public function getCustomInfos()
    {
        return $this->customInfos;
    }

    /**
     * @param string $customInfos
     */
    public function setCustomInfos($customInfos)
    {
        $this->customInfos = $customInfos;
    }

    /**
     * @return boolean
     */
    public function isCommuteAdded()
    {
        return $this->commuteAdded;
    }

    /**
     * @param boolean $commuteAdded
     */
    public function setCommuteAdded($commuteAdded)
    {
        $this->commuteAdded = $commuteAdded;
    }

    /**
     * @return boolean
     */
    public function isCommuteSNCBAdded()
    {
        return $this->commuteSNCBAdded;
    }

    /**
     * @param boolean $commuteSNCBAdded
     */
    public function setCommuteSNCBAdded($commuteSNCBAdded)
    {
        $this->commuteSNCBAdded = $commuteSNCBAdded;
    }

    /**
     * @return boolean
     */
    public function isCommuteSTIBAdded()
    {
        return $this->commuteSTIBAdded;
    }

    /**
     * @param boolean $commuteSTIBAdded
     */
    public function setCommuteSTIBAdded($commuteSTIBAdded)
    {
        $this->commuteSTIBAdded = $commuteSTIBAdded;
    }

    /**
     * @return boolean
     */
    public function isCommuteTECAdded()
    {
        return $this->commuteTECAdded;
    }

    /**
     * @param boolean $commuteTECAdded
     */
    public function setCommuteTECAdded($commuteTECAdded)
    {
        $this->commuteTECAdded = $commuteTECAdded;
    }

    /**
     * @return mixed
     */
    public function getStations()
    {
        return $this->stations;
    }

    /**
     * @param mixed $stations
     */
    public function setStations($stations)
    {
        $this->stations = $stations;
    }

    /**
     * @return string
     */
    public function getMapsImagePath()
    {
        return $this->mapsImagePath;
    }

    /**
     * @param string $mapsImagePath
     */
    public function setMapsImagePath($mapsImagePath)
    {
        $this->mapsImagePath = $mapsImagePath;
    }

}