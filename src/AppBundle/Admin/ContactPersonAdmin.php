<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ContactPersonAdmin extends BaseAdmin {

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('firstname', 'text', array(
                'label' => 'Prénom',
            ))
            ->add('lastname', 'text', array(
                'label' => 'Nom de famille',
            ))
            ->add('phone', 'text', array(
                'label' => 'Numéro de téléphone',
            ))
            ->add('mail', 'text', array(
                'label' => 'Adresse e-mail',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('firstname', 'text', array(
                'label' => 'Prénom',
            ))
            ->add('lastname', 'text', array(
                'label' => 'Nom de famille',
            ))
            ->add('phone', 'text', array(
                'label' => 'Numéro de téléphone',
            ))
            ->add('mail', 'text', array(
                'label' => 'Adresse e-mail',
            ))
            ->add('partners_list', null, array(
                'associated_property' => 'partner',
                'label' => 'Partenaires liés',
                'route' => ['name' => 'nonexistent-route'] // TODO make this more usable by returning the route to the partner admin...
            ))
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('firstname', 'text', array(
                'label' => 'Prénom',
                'required' => true,
            ))
            ->add('lastname', 'text', array(
                'label' => 'Nom de famille',
                'required' => true,
            ))
            ->add('phone', 'text', array(
                'label' => 'Téléphone',
                'required' => false,
            ))
            ->add('mail', 'text', array(
                'label' => 'Adresse e-mail',
                'required' => true,
            ))
        ;
    }

}