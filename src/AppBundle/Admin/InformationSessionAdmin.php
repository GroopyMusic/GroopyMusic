<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Form\AddressType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class InformationSessionAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('show')
            ->remove('delete');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('getName', null, array(
                'label' => 'Nom',
            ))
            ->add('artists', null, array(
                'label' => 'Artistes inscrits',
            ))
            ->add('date', null, array(
                'label' => 'Date',
            ))
            ->add('address', null, array(
                'label' => 'Adresse',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                )
            ))
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
       $form
           ->with('Infos générales')
            ->add('date', null, array(
                'label' => 'Date',
            ))
            ->add('address', AddressType::class, array(
                'label' => 'Adresse'
            ))
           ->end()
           ->with('Champs traductibles')
           ->add('translations', TranslationsType::class, array(
               'locales' => array('fr', 'en'),
               'fields' => [
                   'name' => [
                       'label' => 'Nom de la session',
                   ]
               ]
           ))
           ->end()
       ;
    }
}