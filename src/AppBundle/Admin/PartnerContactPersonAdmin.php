<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;

class PartnerContactPersonAdmin extends BaseAdmin
{
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('contact_person', 'sonata_type_model', array(
                'label' => 'Nom',
            ))
        ;
    }
}