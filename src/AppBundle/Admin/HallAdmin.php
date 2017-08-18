<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;

class HallAdmin extends BaseAdmin {

    public function configureFormFields(FormMapper $form)
    {
        $form->add('name')
            ->add('capacity')
            ->add('step', 'sonata_type_model')
        ;
    }

}