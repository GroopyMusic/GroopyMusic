<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ContactPersonAdmin extends BaseAdmin {

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