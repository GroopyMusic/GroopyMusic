<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User_Conditions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConditionsController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/accept-new-conditions", name="conditions_accept_last")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserInterface $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function acceptLastAction(Request $request, UserInterface $user)
    {
        $form = $this->createFormBuilder()
            ->add('accept', CheckboxType::class, array(
                'required' => true,
                'constraints' => new NotBlank(),
                'label' => $this->get('translator')->trans('labels.conditions.accept_new', ['%conditionsUrl%' => $this->generateUrl('conditions')]),
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.conditions.submit',
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $conditions = $em->getRepository('AppBundle:Conditions')->findLast();
            $user_conditions = new User_Conditions($user, $conditions);
            $em->persist($user_conditions);
            $em->flush();

            $path = $request->getSession()->get('requested_url', $this->generateUrl('homepage'));
            return $this->redirect($path);
        }

        return $this->render('@App/Conditions/accept_last.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
