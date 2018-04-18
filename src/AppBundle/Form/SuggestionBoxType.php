<?php

namespace AppBundle\Form;

use AppBundle\Entity\SuggestionBox;
use AppBundle\Entity\SuggestionTypeEnum;
use AppBundle\Repository\SuggestionTypeEnumRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuggestionBoxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, array(
                'label' => 'labels.suggestionbox.type',
                'class' => SuggestionTypeEnum::class,
                'query_builder' => function (SuggestionTypeEnumRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->leftJoin('t.translations', 'tr')
                        ->orderBy('tr.name', 'ASC');
                },
            ))
            ->add('name', TextType::class, array(
                'label' => 'labels.suggestionbox.name',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 64, 'maxMessage' => 'suggestionbox.name.long']),
                ]
            ))
            ->add('firstname', TextType::class, array(
                'label' => 'labels.suggestionbox.firstname',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 64, 'maxMessage' => 'suggestionbox.firstname.long']),
                ]
            ))
            ->add('email', TextType::class, array(
                'label' => 'labels.suggestionbox.email',
                'required' => false,
                'constraints' => [
                    new Email(['message' => 'suggestionbox.email.email']),
                ]
            ))
            ->add('phone', TextType::class, array(
                'label' => 'labels.suggestionbox.phone',
                'required' => false,
            ))
            ->add('object', TextType::class, array(
                'label' => 'labels.suggestionbox.object',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64, 'maxMessage' => 'suggestionbox.object.long']),
                ]
            ))
            ->add('message', TextareaType::class, array(
                'label' => 'labels.suggestionbox.message',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'suggestionbox.message.blank']),
                    new Length(['min' => 20, 'minMessage' => 'suggestionbox.message.short']),
                ]
            ))
            ->add('mailCopy', CheckboxType::class, array(
                'label' => 'labels.suggestionbox.mailcopy',
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'labels.suggestionbox.submit',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SuggestionBox::class,
        ));
    }

     /**
      * {@inheritdoc}
      */
     public function getBlockPrefix()
     {
         return 'app_suggestionbox';
     }


}
