<?php

namespace XBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FunderController extends Controller
{
    public function indexAction()
    {
        return $this->render('XBundle:Funder:index.html.twig');
    }
}
