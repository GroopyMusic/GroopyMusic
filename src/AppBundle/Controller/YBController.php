<?php

namespace AppBundle\Controller;

use AppBundle\Entity\YB\YBContact;
use AppBundle\Form\YB\YBContactType;
use AppBundle\Services\MailDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class YBController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/", name="yb_index")
     */
    public function indexAction(Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher)
    {
        $contact = new YBContact();
        $form = $this->createForm(YBContactType::class, $contact, ['action' => $this->generateUrl('yb_index') . '#contact']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($contact);
            $em->flush();

            // Mail
            $mailDispatcher->sendYBContactCopy($contact);
            $mailDispatcher->sendAdminYBContact($contact);

            $this->addFlash('yb_notice', 'Thank you for your message. We will come back to you soon.');
            return $this->redirectToRoute('yb_index');
        }

        return $this->render('@App/YB/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
