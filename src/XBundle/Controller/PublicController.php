<?php

namespace XBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicController extends Controller
{
    public function indexAction()
    {
        return $this->render('XBundle:Public:index.html.twig');
    }
}
