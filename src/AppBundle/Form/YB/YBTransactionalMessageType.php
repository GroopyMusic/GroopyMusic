<?php

namespace AppBundle\Form\YB;
use AppBundle\Entity\YB\YBTransactionalMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class YBTransactionalMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'required' => true,
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank(),
                ]
            ))
            ->add('content', TextareaType::class, array(
                'required' => true,
                'label' => 'Contenu',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10]),
                ]
            ))
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
            'data_class' => YBTransactionalMessage::class,
        ));
    }
}