<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 27/03/2018
 * Time: 15:41
 */

namespace AppBundle\Admin;

use AppBundle\Entity\User;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class User_RewardAdmin extends BaseAdmin
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
            ->add('creation_date', null, array(
                'label' => 'Date d\'attribution'
            ))
            ->add('reward', null, array(
                'label' => 'Récompense',
            ))
            ->add('user', null, array(
                'label' => 'Utilisateurs',
                'route' => ['name' => 'show']
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
            ->add('creation_date', null, array(
                'label' => 'Date d\'attribution',
            ))
            ->add('limit_date', null, array(
                'label' => 'Date limite',
            ))
            ->add('user', null, array(
                'label' => 'Utilisateur',
                'route' => ['name' => 'show']
            ))
            ->add('reward', null, array(
                'label' => 'Récompense',
            ))
            ->add('reduction', null, array(
                'label' => 'Réduction',
            ))
            ->add('active', null, array(
                'label' => 'Active',
            ))
            ->add('reward_type_parameters', null, array(
                'label' => 'Paramètre du type de récompenses',
            ))
            ->add('base_contract_artists', null, array(
                'label' => 'Concert(s) lié(s)',
            ))
            ->add('base_steps', null, array(
                'label' => 'Palier(s) de salle lié(s)',
            ))
            ->add('counter_parts', null, array(
                'label' => 'Achat(s) lié(s)',
            ))
            ->add('artists', null, array(
                'label' => 'Artiste(s) lié(s)',
            ));

    }
}