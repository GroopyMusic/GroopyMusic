<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Hall;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAjaxController extends Controller
{
    /**
     * @Route("/all-{id}/remove-photo", name="admin_ajax_hall_remove_photo")
     */
    public function hallRemovePhotoAction(Request $request, Hall $hall) {

        $em = $this->getDoctrine()->getManager();

        $filename = $request->get('filename');
        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['filename' => $filename]);

        $em->remove($photo);
        $hall->removePhoto($photo);

        $filesystem = new Filesystem();
        $filesystem->remove($this->get('kernel')->getRootDir().'/../web/' . Hall::getWebPath($photo));

        $em->persist($hall);
        $em->flush();

        return new Response();
    }
}
