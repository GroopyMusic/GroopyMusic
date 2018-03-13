<?php

namespace AppBundle\Controller;

use AppBundle\Entity\VIPInscription;
use AppBundle\Form\VIPInscriptionType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TempShitController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/presse", name="press")
     */
    public function VIPInscriptionAction(Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher) {

        $inscription = new VIPInscription();
        $BD = $em->getRepository('AppBundle:ContractArtist')->findOneById($this->container->getParameter('vip_inscription_event_id'));
        $inscription->setContractArtist($BD);

        $form = $this->createForm(VIPInscriptionType::class, $inscription);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($inscription);
            $em->flush();
            $this->addFlash('notice', 'Votre inscription a bien été enregistrée. Au plaisir de vous voir le 10 avril prochain !');

            try {
                $mailDispatcher->sendAdminVIPInscription($inscription);
                $notificationDispatcher->notifyAdminVIPInscription($inscription);
                $mailDispatcher->sendVIPInscriptionCopy($inscription);
            } catch(\Exception $e) {

            }

            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@App/Public/Temp/vip_inscription.html.twig', array(
            'form' => $form->createView(),
            'inscription' => $inscription,
        ));
    }

    /**
     * @Route("/test", name="testpage")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function testPageAction(TicketingManager $manager, UserInterface $user, EntityManagerInterface $em) {
        $contractArtist = $em->getRepository('AppBundle:ContractArtist')->find(14);
        $manager->getTicketPreview($contractArtist, $user);

        return new Response('OK');
    }


    /**
     * @Route("/test-mail", name="testmail")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function testMailAction(KernelInterface $kernel) {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'reminders:crowdfunding:artist',
            'x' => '1',
        ));

        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();

        // return new Response(""), if you used NullOutput()
        return new Response($content);
    }
}
