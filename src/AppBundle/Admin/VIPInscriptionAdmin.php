<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class VIPInscriptionAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('edit')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, array('label' => '#'))
            ->add('last_name', null, array('label' => 'Nom'))
            ->add('first_name', null, array('label' => 'Prénom'))
            ->add('email', null, array('label' => 'Email'))
            ->add('company', null, array('label' => 'Société'))
            ->add('function', null, array('label' => 'Fonction'))
            ->add('contractArtist', null, array('label' => 'Event'))
            ->add('_action', null, [
                'actions' => [
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, array('label' => '#'))
            ->add('last_name', null, array('label' => 'Nom'))
            ->add('first_name', null, array('label' => 'Prénom'))
            ->add('email', null, array('label' => 'Email'))
            ->add('company', null, array('label' => 'Société'))
            ->add('function', null, array('label' => 'Fonction'))
            ->add('contractArtist', null, array('label' => 'Event'))
        ;
    }

    public function getExportFields() {

        return [
            '#' => 'id',
            'Nom' => 'displayName',
            'Société' => 'company',
            'Fonction' => 'function',
            'Event' => 'contractArtist',
        ];
    }
}
