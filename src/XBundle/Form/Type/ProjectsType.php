<?php

namespace XBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XBundle\Form\Type\ImageType;
use XBundle\Form\Type\TagsType;
use AppBundle\Form\VideoType;

class ProjectsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',	TextType::class)->add('description', 	TextType::class)->add('fund', 	IntegerType::class)->add('photo', 	ImageType::class)->add('tags', TagsType::class)->add('submit', 	SubmitType::class);

        /*->add('videos', CollectionType::class, array(
                'label' => false,
                'entry_type' => VideoType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
            ))*/
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'XBundle\Entity\Projects'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_projects';
    }


}
