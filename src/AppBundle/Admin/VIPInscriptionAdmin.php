<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class VIPInscriptionAdmin extends BaseAdmin
{
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
            ->add('counterparts_sent', null, array('label' => 'Tickets envoyés ?'))
            ->add('_action', null, [
                'actions' => [
                    'delete' => [],
                    'edit' => [],
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
            ->add('counterparts_sent', null, array('label' => 'Tickets envoyés ?'))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('last_name', null, array('label' => 'Nom'))
            ->add('first_name', null, array('label' => 'Prénom'))
            ->add('email', null, array('label' => 'Email'))
            ->add('company', null, array('label' => 'Société'))
            ->add('function', null, array('label' => 'Fonction'))
            ->add('contract_artist', null, array('label' => 'Event'))
            ->add('counterparts_sent', null, array('label' => 'Tickets envoyés ?'))
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
