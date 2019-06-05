<?php

namespace XBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use XBundle\Repository\ProductRepository;
use XBundle\Entity\Product;
use XBundle\Entity\XTransactionalMessage;


class XTransactionalMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Titre du message',
                'constraints' => [
                    new NotBlank(),
                ]
            ))
            ->add('content', TextareaType::class, array(
                'label' => 'Contenu du message',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10]),
                ]
            ))
            ->add('beforeValidation', CheckboxType::class, array(
                'label' => 'Avant validation',
                'required' => false,
            ))
            ->add('toDonators', CheckboxType::class, array(
                'label' => 'Destiné uniquement aux donateurs',
                'required' => false,
            ))
            ->add('toBuyers', CheckboxType::class, array(
                'label' => 'Destiné uniquement aux acheteurs',
                'required' => false,
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $message = $event->getData();
                $event->getForm()->add('products', Select2EntityType::class, array(
                    'required' => false,
                    'label' => false,
                    'multiple' => true,
                    'remote_route' => 'select2_transactional_message_products',
                    'remote_params' => ['project' => $message->getProject()->getId()],
                    'class' => 'XBundle\Entity\Product',
                    'primary_key' => 'id',
                ));
            })
            ->add('submit', SubmitType::class, array(
                'label' => '<i class="fas fa-check"></i> Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' =>XTransactionalMessage::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_xtransactional_message_type';
    }


}
