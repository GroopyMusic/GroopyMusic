<?php

namespace AppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class UtilitiesExtension extends \Twig_Extension
{
    private $twig;
    private $stringHelper;
    private $em;

    public function __construct(Environment $twig, StringHelper $stringHelper, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->stringHelper = $stringHelper;
        $this->em = $em;
    }

    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('instanceof', array($this, 'isInstanceOf')),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('decode_html', array($this, 'decode')),
            new \Twig_SimpleFilter('slug', array($this, 'slugify')),
            new \Twig_SimpleFilter('fancy_date', array($this, 'fancy_date')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('hidden_loader', array($this, 'hidden_loader'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('preview', array($this, 'preview', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('error', array($this, 'error', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('email_content', array($this, 'email_content', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('notification_preview', array($this, 'notification_preview', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('notification_menu_preview', array($this, 'notification_menu_preview', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('notification_complete', array($this, 'notification_complete', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('email_link', array($this, 'email_link', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('facebook_like_widget', array($this, 'facebook_like_widget', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('facebook_connect_widget', array($this, 'facebook_connect_widget', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('lorem_ipsum', array($this, 'lorem_ipsum', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('fancy_date', array($this, 'fancy_date', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('yb_asset', array($this, 'yb_asset', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('base64', array($this, 'base64')),
            new \Twig_SimpleFunction('yb_error', array($this, 'yb_error', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('progress', array($this, 'progress', array('is_safe' => array('html')))),
            new \Twig_SimpleFunction('festival_link', array($this, 'festival_link', array('is_safe' => array('html')))),
        );
    }

    public function isInstanceOf($var, $instance) {
        $reflexionClass = new \ReflectionClass($instance);
        return $reflexionClass->isInstance($var);
    }

    public function decode($value)
    {
        return strip_tags(html_entity_decode($value));
    }

    public function hidden_loader($hidden = true, $um = true) {
        return $this->twig->render('patterns/utils/hidden_loader.html.twig', [
            'hidden' => $hidden,
            'um' => $um,
        ]);
    }

    public function preview($text, $limit) {
        return $this->twig->render('patterns/utils/preview.html.twig', [
            'text' => $text,
            'limit' => $limit,
        ]);
    }

    public function error($message, $dismissible = true) {
        return $this->twig->render('patterns/utils/error.html.twig', [
            'message' => $message,
            'dismissible' => $dismissible,
        ]);
    }

    public function notification_preview($notification, $title, $content) {
        return $this->twig->render('AppBundle:Notifications/utils:preview.html.twig', array(
            'notification' => $notification,
            'title' => $title,
            'content' => $content,
        ));
    }

    public function notification_menu_preview($notification, $title, $content) {
        return $this->twig->render('AppBundle:Notifications/utils:menu_preview.html.twig', array(
            'notification' => $notification,
            'title' => $title,
            'content' => $content,
        ));
    }

    public function notification_complete($notification, $title, $content) {
        return $this->twig->render('AppBundle:Notifications/utils:complete.html.twig', array(
            'notification' => $notification,
            'title' => $title,
            'content' => $content,
        ));
    }

    public function email_content($email_key, $email_variables) {
        return $this->twig->render(':patterns/utils:email.html.twig', array(
            'email_key' => $email_key,
            'email_variables' => $email_variables,
        ));
    }

    public function email_link($url, $text = null) {
        if($text == null) {
            $text = $url;
        }
        return $this->twig->render(':patterns/utils:email_link.html.twig', array(
            'url' => $url,
            'text' => $text,
        ));
    }

    public function facebook_like_widget($with_faces = false) {
        return $this->twig->render(':patterns/utils:facebook_like_widget.html.twig', array(
            'with_faces' => $with_faces,
        ));
    }

    public function facebook_connect_widget($with_pp = true) {
        return $this->twig->render(':patterns/utils:facebook_connect_widget.html.twig', array(
            'with_pp' => $with_pp,
        ));
    }

    public function lorem_ipsum($lorem_nb = 1) {
        return $this->twig->render(':patterns/utils:lorem_ipsum.html.twig', array(
            'lorem_nb' => $lorem_nb,
        ));
    }

    public function slugify($str) {
        return $this->stringHelper->slugify($str);
    }

    public function fancy_date($date, $strip_year = false) {
        return $this->twig->render(':patterns/utils:fancy_date.html.twig', array(
            'date' => $date,
            'strip_year' => $strip_year,
        ));
    }

    public function yb_asset($url) {
        return $this->twig->render(':patterns/utils:yb_asset.html.twig', array(
            'url' => $url,
        ));
    }

    public function base64($url) {
        return ImageHelper::base64($url);
    }

    public function yb_error($message) {
        return $this->twig->render(':patterns/utils:yb_error.html.twig', array(
            'message' => $message,
        ));
    }

    public function progress($progress_100) {
        return $this->twig->render(':patterns/utils:progress.html.twig', array(
            'progress_100' => $progress_100,
        ));
    }

    public function festival_link() {
        $festivals = $this->em->getRepository('AppBundle:ContractArtist')->findVisible();
        if(empty($festivals))  {
            $festival = null;
        }
        else {
            $festival = $festivals[0];
        }
        return $this->twig->render(':patterns/utils:festival_link.html.twig', array(
            'festival' => $festival,
        ));
    }


}