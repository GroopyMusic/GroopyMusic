<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 25/04/2018
 * Time: 12:00
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class SponsorshipInvitationAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('edit');
    }
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('date_invitation', null, array(
                'label' => 'Date d\'invitation'
            ))
            ->add('host_invitation', null, array(
                'label' => 'Parrain',
                'route' => ['name' => 'show']
            ))
            ->add('email_invitation', null, array(
                'label' => 'Email invité',
            ))
            ->add('confirmed', 'boolean', array(
                'label' => 'Confirmé',
            ))
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                    )
                )
            );
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('date_invitation', null, array(
                'label' => 'Date d\'attribution',
            ))
            ->add('host_invitation', null, array(
                'label' => 'Parrain',
                'route' => ['name' => 'show']
            ))
            ->add('email_invitation', null, array(
                'label' => 'Email d\'invitation',
            ))
            ->add('text_invitation', null, array(
                'label' => 'Texte d\'invitation'
            ))
            ->add('target_invitation', null, array(
                'label' => 'Parrainé',
            ))
            ->add('contract_artist', null, array(
                'label' => 'Evénement lié',
            ))
            ->add('confirmed', 'boolean', array(
                'label' => 'Confirmé',
            ))
            ->add('reward_sent', null, array(
                'label' => 'Récompenses de parrainage attribuée',
            ));
    }
}