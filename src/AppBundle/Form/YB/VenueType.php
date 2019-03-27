<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\Venue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VenueType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add();
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => Venue::class,
        ));
    }

}