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

            ->add('contractArtist', null, array(
                'label' => 'Evenement',
            ))
            ->add('is_child_entry', null, array(
                'label' => 'Ticket enfant'
            ))
            ->add('free_price', null, array(
                'label' => 'Prix libre'
            ))
            ->add('semanticPrice', null, array(
                'label' => 'Prix'
            ))
            ->add('festivaldays', null, array(
                'label' => 'Jours de festival',
            ))
            ->add('maximum_amount', null, array(
                'label' => 'Sold out',
            ))
            ->add('threshold_increase', null, array(
                'label' => 'Poids',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
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
            ->add('contractArtist', null, array(
                'label' => 'Evenement',
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
                ->add('free_price', null, array(
                    'label' => 'Prix libre ?',
                    'required' => false,
                ))
                ->add('price', null, array(
                    'label' => 'Prix',
                    'required' => true,
                ))
                ->add('minimum_price', null, array(
                    'label' => 'Prix minimum',
                    'required' => true,
                ))
                ->add('threshold_increase', null, array(
                    'label' => 'Contribution au crowdfunding & au soldout',
                ))
                ->add('contractArtist', null, array(
                    'label' => 'Event',
                    'required' => true,
                ))
                ->add('festivaldays', null, array(
                    'label' => 'Jours de festival auxquels ce ticket donne accès',
                ))
                ->add('is_child_entry', null, array(
                    'label' => 'Ticket enfant',
                    'required' => false,
                ))
                ->add('maximum_amount', null, array(
                    'label' => 'Nombre max de ventes',
                    'required' => true,
                ))
            ->end()
        ;
    }
}