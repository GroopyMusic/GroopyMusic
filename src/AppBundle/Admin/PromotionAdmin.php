<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class PromotionAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('start_date', null, array(
                'label' => 'Date de début',
                'editable' => true,
            ))
            ->add('end_date', null, array(
                'label' => 'Date de fin',
                'editable' => true,
            ))
            ->add('type', null, array(
                'label' => 'Type'
            ))
            ->add('contracts', null, array(
                'label' => 'Event(s)'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
            ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('start_date', null, array(
                'label' => 'Date de début'
            ))
            ->add('end_date', null, array(
                'label' => 'Date de fin'
            ))
            ->add('type', null, array(
                'label' => 'Type (disponibles : three_plus_one, six_plus_one, ten_plus_two - s\'adresser à Gonzague pour en ajouter d\'autres)'
            ))
        ;
    }


}