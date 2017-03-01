<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends Controller
{
    public function registerAction() {
        return $this->render('AppBundle:Public/Security:registration.html.twig');
    }

    public function registerArtistAction() {

        return $this->container
            ->get('pugx_multi_user.registration_manager')
            ->register('AppBundle\Entity\UserArtist');
    }

    public function registerFanAction() {

        return $this->container
            ->get('pugx_multi_user.registration_manager')
            ->register('AppBundle\Entity\UserFan');
    }
}
