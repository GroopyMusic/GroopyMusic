<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class FestivalDayAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('date', null, array(
                'label' => 'Date',
            ))
            ->add('festivals', null, array(
                'label' => 'Evenement(s)',
            ))
            ->add('hall', null, array(
                'label' => 'Salle'
            ))
            ->add('performances', null, array(
                'label' => 'Performances',
            ))
            ->add('tickets_sold', null, array(
                'label' => 'Nb tickets vendus'
            ))
            ->add('global_soldout', null, array(
                'label' => 'Soldout global'
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
            ->add('date', null, array(
                'label' => 'Date',
            ))
            /* y a un problème avec la persistence des entités ça m'a gavé
            ->add('festivals', null, array(
                'label' => 'Evenement(s)',
            ))*/
            ->add('hall', null, array(
                'label' => 'Salle'
            ))/*
            ->add('performances', null, array(
                'label' => 'Performances',
            ))*/
            ->add('global_soldout', null, array(
                'label' => 'Soldout global'
            ))
        ;
    }
}