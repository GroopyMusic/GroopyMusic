<?php

namespace XBundle\Form;

use AppBundle\Entity\Artist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use XBundle\Entity\Project;
use XBundle\Entity\XCategory;
use XBundle\Form\XAddressType;
use XBundle\Form\ImageType;
use XBundle\Form\TagType;

class ProjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Titre du projet',
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('artist', EntityType::class, array(
                'class' => Artist::class,
                'label' => 'Artiste associé',
                'choices' => $options['artists_user'],
                'choice_label' => 'artistname',
                'disabled' => $options['is_edit'],
                'placeholder' => '',
                'empty_data' => null,
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('description', 'ckeditor', array(
                'label' => 'Description',
                'config_name' => 'bbcode',
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('motivations', 'ckeditor', array(
                'label' => 'Motivations',
                'config_name' => 'bbcode',
                'required' => false
            ))
            ->add('thresholdPurpose', 'ckeditor', array(
                'label' => 'A quoi servira le financement du projet',
                'config_name' => 'bbcode',
                'required' => false
            ))
            ->add('dateEnd', DateTimeType::class, array(
                'label' => 'Date de clôture du financement participatif',
                'disabled' => $options['is_edit'],
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('dateEvent', DateTimeType::class, array(
                'required' => false,
                'label' => "Date de l'événement"
            ))
            ->add('address', XAddressType::class, array(
                'required' => false,
                'label' => "Lieu de l'événement",
                /*'constraints' => [
                    new Assert\Valid(),
                ]*/
            ))
            ->add('threshold', IntegerType::class, array(
                'label' => 'Montant à atteindre (en €)',
                'disabled' => $options['is_edit'],
                'required' => false,
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0])
                ]
            ))
            ->add('category', EntityType::class, array(
                'label' => 'Catégorie',
                'class' => XCategory::class,
                'choice_label' => 'name',
                'choice_value' => 'name',
                'placeholder' => '',
                'empty_data' => null,
                'disabled' => $options['is_edit'],
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('tags', TagType::class, array(
                'label' => 'Tags',
                'required' => false
            ))
            ->add('coverpic', ImageType::class, array(
                'label' => 'Photo de couverture',
                'required' => false
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer'
            ))
        ;

        if ($options['creation']) {
            $builder
                ->add('noThreshold', CheckboxType::class, array(
                    'label' => 'Pas de seuil de validation',
                    'required' => false
                ))
                ->add('acceptConditions', CheckboxType::class, array(
                    'label' => "J'ai lu et j'accepte les conditions d'utilisation de la plateforme Chapots.",
                    'required' => true,
                    'constraints' => array(
                        new Assert\NotBlank(),
                    )
                ))
            ;
        }
    }


    public function validate(Project $project, ExecutionContextInterface $context)
    {
        if($project->getDateEnd() != null && $project->getDateEnd() < $project->getDateCreation()) {
            $context->addViolation('La date de clôture du financement du projet doit être dans le futur.');
        }

        if($project->getCategory()->getName() == "Évènement") {
            if($project->getDateEvent() == null) {
                $context->addViolation('Il faut renseigner une date pour l\'évènement');
            }
            if($project->getDateEvent() != null && $project->getDateEvent() < $project->getDateEnd()) {
                $context->addViolation('La date de l\'évènement doit être postérieur à celle de clôture du financement du projet.');
            }
            if($project->getAddress() == null) {
                $context->addViolation('Il faut renseigner une adresse pour le lieu de l\'évènement');
            }
        } else {
            $project->setDateEvent(null);
            $project->setAddress(null);
        }

        if($project->hasThreshold() && $project->getThreshold() <= 0) {
            $context->addViolation('Puisque le projet à un seuil de validation, il faut préciser ce seuil, qui doit être supérieur à 0.');
        } else {
            // In case project is noThreshold but artist owner forgets to clear threshold field
            if($project->getNoThreshold()) {
                $project->setThreshold(null);
            }
        }
        
    }

    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'creation' => false,
            'is_edit'=> false,
            'artists_user' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_project_type';
    }


}
