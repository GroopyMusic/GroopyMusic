<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\YBSubEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class YBSubEventType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateTimeType::class, array(
                'required' => true,
                'label' => false,
            ))
        ;
    }

    public function validate(YBSubEvent $subEvent, ExecutionContextInterface $context)
    {
        if($subEvent->getDate() < (new \DateTime())) {
            $context->addViolation('Les dates de la campagne doivent être dans le futur.');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => YBSubEvent::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_yb_sub_event_type';
    }
}
