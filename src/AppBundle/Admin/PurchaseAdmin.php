<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PurchaseAdmin extends AbstractAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Infos')
                ->add('contractFan.user', null, array(
                    'label' => 'Membre',
                ))
                ->add('quantity', null, array(
                    'label' => 'Quantité totale',
                ))
                ->add('getQuantityOrganic', null, array(
                    'label' => 'dont contreparties payées',
                ))
                ->add('nb_free_counterparts', null, array(
                    'label' => 'et contreparties offertes par promotion',
                ))
                ->add('counterpart', null, array(
                    'label' => 'Contrepartie',
                    'route' => ['name' => 'show'],
                ))
                ->add('contractFan', null, array(
                    'label' => 'Commande correspondante',
                    'route' => ['name' => 'show'],
                ))
            ->end()
        ;
    }
}
