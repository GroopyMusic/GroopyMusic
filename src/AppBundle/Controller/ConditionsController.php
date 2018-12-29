<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User_Conditions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConditionsController extends BaseController
{
    /**
     * Conditions: displays last version of terms of use
     * @Route("/", name="conditions")
     */
    public function conditionsAction() {
        $last_terms = $this->getDoctrine()->getManager()->getRepository('AppBundle:Conditions')->findLast();
        return $this->render('AppBundle:Conditions:conditions.html.twig', array(
            'last_terms' => $last_terms,
        ));
    }

    /**
     * Accept Last: legally mandatory page to accept terms of use after these have received a change
     * @Route("/accept-new-conditions", name="conditions_accept_last")
     */
    public function acceptLastAction(Request $request, UserInterface $user)
    {
        $form = $this
            ->createFormBuilder()
            ->add('accept', CheckboxType::class, array(
                'required' => true,
                'constraints' => new NotBlank(),
                'label' => $this->get('translator')->trans('labels.conditions.accept_new', ['%conditionsUrl%' => $this->generateUrl('conditions')]),
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.conditions.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ))
            ->getForm()
        ;

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
