<?php

namespace AppBundle\Admin;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PhaseAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('getName', null, array(
                'label' => 'Nom'
            ))
            ->add('num', null, array(
                'label' => "Numéro d'ordre",
            ))
            ->add('steps', null, array(
                'label' => 'Paliers',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('getName', null, array(
                'label' => 'Nom'
            ))
            ->add('num', null, array(
                'label' => "Numéro d'ordre",
            ))
            ->add('steps', null, array(
                'label' => 'Paliers',
            ))
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
                ->add('num', null, array(
                    'label' => "Numéro d'ordre de la phase",
                ))
            ->end()
            ->with('Champs traductibles')
                ->add('translations', TranslationsType::class, array(
                    'fields' => [
                        'name' => [
                            'label' => 'Nom de la phase'
                        ]
                    ],
                    'label' => false,
                ))
            ->end()
        ;
    }

}