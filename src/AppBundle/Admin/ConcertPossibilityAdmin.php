<?php

namespace AppBundle\Admin;

use AppBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class ConcertPossibilityAdmin extends BaseAdmin
{
    public function configureFormFields(FormMapper $form) {
        $form
            ->add('hall', 'sonata_type_model')
            ->add('date');
    }


}
