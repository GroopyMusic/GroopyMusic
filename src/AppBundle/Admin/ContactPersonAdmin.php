<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ContactPersonAdmin extends BaseAdmin {

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('phone', 'text')
            ->add('mail', 'text')
            ->add('_action', 'actions', array(
                'edit' => array(),
                'delete' => array(),
            ))
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('phone', 'text')
            ->add('mail', 'text')
        ;
    }

}