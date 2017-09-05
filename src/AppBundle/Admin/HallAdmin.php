<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class HallAdmin extends PartnerAdmin  {

    public function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);
        $listMapper
            ->remove('type')
            ->add('step')
            ->add('capacity')
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        parent::configureFormFields($form);
        $form->add('name')
            ->add('capacity')
            ->add('cron_automatic_days', null, array(
                'entry_type' => 'checkbox',
                'entry_options' => [
                    'required' => false,
                ]
            ))
            ->add('available_dates_string', 'text', array(
                'empty_data' => '',
                'required' => false,
                'attr' => ['class' => 'multiDatesPicker'],
            ))
            ->add('step', 'sonata_type_model')
        ;
    }

    public function prePersist($object)
    {
        parent::prePersist($object);
        $object->refreshDates();
    }

    public function preUpdate($object)
    {
        parent::preUpdate($object);
        $object->refreshDates();
    }

}