<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class VolunteerProposalAdmin extends BaseAdmin
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
            ->add('contractArtist', null, array('label' => 'Event'))
            ->add('counterparts_sent', null, array('label' => 'Tickets envoyés ?'))
            ->add('commentary', null, array('label' => 'Commentaire'))
            ->add('_action', null, [
                'actions' => [
                    'delete' => [],
                ],
            ])
        ;
    }

    public function getExportFields() {

        return [
            '#' => 'id',
            'Nom' => 'displayName',
            'Email' => 'email',
            'Commentaire' => 'commentary',
            'Event' => 'contractArtist',
        ];
    }
}
