<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('displayName', null, array(
                'label' => 'Nom complet',
            ))
            ->add('email', null, array(
                'label' => 'Adresse e-mail',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                )))
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Profil')
                ->add('id', null)
                ->add('inscription_date', null, array(
                    'format' => 'd/m/y H:i:s',
                    'label' => "Date d'inscription"
                ))
                ->add('lastname', null, array(
                    'label' => 'Nom de famille',
                ))
                ->add('firstname', null, array(
                    'label' => 'Prénom',
                ))
                ->add('newsletter', null, array(
                    'label' => 'Inscrit à la newsletter',
                ))
                ->add('genres', null, array(
                    'label' => 'Genres musicaux',
                ))
                ->add('address', null, array(
                    'label' => 'Adresse',
                ))
            ->end()
            ->with('Activité')
                ->add('payments', null, array(
                    'label' => 'Paiements',
                ))
                ->add('carts', null, array(
                    'label' => 'Paniers',
                ))
                ->add('stripe_customer_id', null, array(
                    'label' => 'Stripe customer ID',
                ))
                ->add('credits', null, array(
                    'label' => 'Crédits obtenus',
                ))
                ->add('specialPurchases', null, array(
                    'label' => 'Achats avec crédits bonus',
                ))
            ->end()
        ;
    }
}