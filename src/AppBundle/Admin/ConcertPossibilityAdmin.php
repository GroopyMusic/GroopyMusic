<?php

namespace AppBundle\Admin;

use AppBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ConcertPossibilityAdmin extends BaseAdmin
{
    public function configureFormFields(FormMapper $form) {
        $form
            ->add('hall', 'sonata_type_model', array(
                'btn_add' => false,
            ))
            ->add('date', 'date', array(
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'attr' => ['class' => 'datePicker'],
            ))
        ;
    }
}
