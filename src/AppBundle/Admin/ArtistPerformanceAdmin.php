<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ArtistPerformanceAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('artist', null, array(
                'label' => 'Artiste'
            ))
            ->add('festivalday', null, array(
                'label' => 'Jour de festival',
            ))
            ->add('time', null, array(
                'label' => 'Moment de la performance'
            ))
            ->add('duration', null, array(
                'label' => 'Durée'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                )))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('artist', null, array(
                'label' => 'Artiste'
            ))
            ->add('festivalday', null, array(
                'label' => 'Jour de festival',
            ))
            ->add('time', null, array(
                'label' => 'Moment de la performance'
            ))
            ->add('duration', null, array(
                'label' => 'Durée (minutes)'
            ))
        ;
    }
}