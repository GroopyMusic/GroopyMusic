<?php

namespace AppBundle\Controller;

use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Form\YB\YBContractArtistType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;

class YBMembersController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/dashboard", name="yb_members_dashboard")
     */
    public function dashboardAction()
    {
        throw $this->createAccessDeniedException();
        $campaign = new YBContractArtist();
        $form = $this->createForm(YBContractArtistType::class, $campaign);
        return $this->render('@App/YB/Members/dashboard.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
