<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CounterPartAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('getName', null, array(
                'label' => 'Nom'
            ))
            ->add('price', null, array(
                'label' => 'Prix'
            ))
            ->add('step', null, array(
                'label' => 'Palier'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                )))
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('getName', null, array(
                'label' => 'Name',
            ))
            ->add('price', null, array(
                'label' => 'Prix'
            ))
            ->add('step', null, array(
                'label' => 'Palier'
            ))
            ->add('maximum_amount', null, array(
                'label' => 'Nombre max de ventes'
            ))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Champs traductibles')
                ->add('translations', TranslationsType::class, array(
                    'fields' => [
                        'name' => [
                            'label' => 'Nom',
                        ],
                    ],
                ))
            ->end()
            ->with('Autres')
                ->add('price', null, array(
                    'label' => 'Prix',
                    'required' => true,
                ))
                ->add('step', null, array(
                    'label' => 'Palier',
                    'required' => true,
                ))
                ->add('maximum_amount', null, array(
                    'label' => 'Nombre max de ventes',
                    'required' => true,
                ))
            ->end()
        ;
    }
}