<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artistname', TextType::class)
            ->add('short_description', TextareaType::class)
            ->add('biography', TextareaType::class)
            ->add('genres', Select2EntityType::class, [
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => 'AppBundle\Entity\Genre',
                'primary_key' => 'id',
                'text_property' => 'name',
            ])
            ->add('submit', SubmitType::class)
        ;
    }


    public function getBlockPrefix()
    {
        return 'app_user_artist';
    }
}
