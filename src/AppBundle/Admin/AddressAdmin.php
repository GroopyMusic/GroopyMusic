<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class AddressAdmin extends BaseAdmin {
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('street')
            ->add('number')
            ->add('zipcode')
            ->add('city')
            ->add('country', 'country')
        ;
    }
}