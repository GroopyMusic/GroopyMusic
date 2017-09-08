<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Video
 *
 * @ORM\Table(name="video")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VideoRepository")
 */
class Video
{

    public function __construct()
    {

    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload) {
        if(!$this->getVideoLocation()) {
            $context
                ->buildViolation("Cette URL n'est pas reconnue. Assurez-vous de référencer une vidéo youtube, vimeo ou dailymotion.")
                ->atPath('url')
                ->addViolation()
            ;
        }
    }

    /**
     * Gets the thumbnail url associated with an url from either:
     *
     *      - youtube
     *      - daily motion
     *      - vimeo
     *
     * Returns false if the url couldn't be identified.
     *
     * In the case of you tube, we can use the second parameter (format), which
     * takes one of the following values:
     *      - small         (returns the url for a small thumbnail)
     *      - medium        (returns the url for a medium thumbnail)
     *
     *
     *
     */
    public function getVideoThumbnailByUrl($format = 'small')
    {
        $url = $this->url;

        if (false !== ($id = $this->getVimeoId($url))) {
            $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$id.php"));
            /**
             * thumbnail_small
             * thumbnail_medium
             * thumbnail_large
             */
            return $hash[0]['thumbnail_large'];
        }
        elseif (false !== ($id = $this->getDailyMotionId($url))) {
            return 'http://www.dailymotion.com/thumbnail/video/' . $id;
        }
        elseif (false !== ($id = $this->getYoutubeId($url))) {
            /**
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/0.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/1.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/2.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/3.jpg
             *
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/default.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/hqdefault.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/mqdefault.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/sddefault.jpg
             * http://img.youtube.com/vi/<insert-youtube-video-id-here>/maxresdefault.jpg
             */
            if ('medium' === $format) {
                return 'http://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
            }
            return 'http://img.youtube.com/vi/' . $id . '/default.jpg';
        }
        return false;
    }

    /**
     * Returns the location of the actual video for a given url which belongs to either:
     *
     *      - youtube
     *      - daily motion
     *      - vimeo
     *
     * Or returns false in case of failure.
     * This public function can be used for creating video sitemaps.
     */
    public function getVideoLocation()
    {
        $url = $this->url;
        if (false !== ($id = $this->getDailyMotionId($url))) {
            return 'http://www.dailymotion.com/embed/video/' . $id;
        }
        elseif (false !== ($id = $this->getVimeoId($url))) {
            return 'http://player.vimeo.com/video/' . $id;
        }
        elseif (false !== ($id = $this->getYoutubeId($url))) {
            return 'http://www.youtube.com/embed/' . $id;
        }
        return false;
    }

    /**
     * Returns the html code for an embed responsive video, for a given url.
     * The url has to be either from:
     * - youtube
     * - daily motion
     * - vimeo
     *
     * Returns false in case of failure
     */
    public function getEmbedVideo()
    {
        $url = $this->url;

        $code = <<<EEE
    <style>
        .embed-container { 
            position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; 
        }
        .embed-container iframe, .embed-container object, .embed-container embed { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
        }
    </style>
EEE;


        if (false !== ($id = $this->getDailyMotionId($url))) {
            $code .= <<<EEE
<div class='embed-container'><iframe src='http://www.dailymotion.com/embed/video/$id' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>
EEE;

        }
        elseif (false !== ($id = $this->getVimeoId($url))) {
            $code .= <<<EEE
<div class='embed-container'><iframe src='http://player.vimeo.com/video/$id' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>
EEE;
        }
        elseif (false !== ($id = $this->getYoutubeId($url))) {
            $code .= <<<EEE
<div class='embed-container'><iframe src='http://www.youtube.com/embed/$id' frameborder='0' allowfullscreen></iframe></div>
EEE;
        }
        else {
            $code = false;
        }
        return $code;
    }

    /**
     * Extracts the daily motion id from a daily motion url.
     * Returns false if the url is not recognized as a daily motion url.
     */
    public function getDailyMotionId($url)
    {

        if (preg_match('!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $m)) {
            if (isset($m[6])) {
                return $m[6];
            }
            if (isset($m[4])) {
                return $m[4];
            }
            return $m[2];
        }
        return false;
    }


    /**
     * Extracts the vimeo id from a vimeo url.
     * Returns false if the url is not recognized as a vimeo url.
     */
    public function getVimeoId($url)
    {
        if (preg_match('#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $m)) {
            return $m[1];
        }
        return false;
    }

    /**
     * Extracts the youtube id from a youtube url.
     * Returns false if the url is not recognized as a youtube url.
     */
    public function getYoutubeId($url)
    {
        $parts = parse_url($url);
        if (isset($parts['host'])) {
            $host = $parts['host'];
            if (
                false === strpos($host, 'youtube') &&
                false === strpos($host, 'youtu.be')
            ) {
                return false;
            }
        }
        if (isset($parts['query'])) {
            parse_str($parts['query'], $qs);
            if (isset($qs['v'])) {
                return $qs['v'];
            }
            else if (isset($qs['vi'])) {
                return $qs['vi'];
            }
        }
        if (isset($parts['path'])) {
            $path = explode('/', trim($parts['path'], '/'));
            return $path[count($path) - 1];
        }
        return false;
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
     * @var string
     *
     * @ORM\Column(name="url", type="text")
     */
    private $url;


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
     * Set url
     *
     * @param string $url
     *
     * @return Video
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}

