<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;

class ContractArtistArtistAdmin extends BaseAdmin
{
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('artist', 'sonata_type_model', array(
            ))
        ;

    }
}