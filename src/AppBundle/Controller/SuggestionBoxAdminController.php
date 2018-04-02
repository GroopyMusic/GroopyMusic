<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SuggestionBox;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class SuggestionBoxAdminController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->configure();
    }

    public function handleAction(UserInterface $user)
    {
        /** @var SuggestionBox $contactform */
        $contactform = $this->admin->getSubject();
        $em = $this->getDoctrine()->getManager();

        if($contactform->getHandler() != null) {
            $this->addFlash('sonata_flash_error', 'Ce cas est déjà géré par un autre admin.');
        }
        else {
            $contactform->setHandler($user);
            $this->addFlash('sonata_flash_success', 'Tu as bien été marqué comme responsable de ce cas.');
            $em->persist($contactform);
            $em->flush();
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
