<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Select2Controller extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/genres", name="select2_genres")
     */
    public function genresAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $q = $request->get('q');
        $genres = $em->getRepository('AppBundle:Genre')->findForString($q, $request->getLocale());

        $genresArray = [];

        foreach($genres as $genre)
        {
            $genresArray[] = array(
                'id' => $genre->getId(),
                'text' => $genre->getName(),
            );
        }
        return new Response(json_encode($genresArray), 200, array('Content-Type' => 'application/json'));
    }
}
