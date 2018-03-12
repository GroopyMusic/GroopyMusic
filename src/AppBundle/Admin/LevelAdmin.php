<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 14:18
 */

namespace AppBundle\Admin;


namespace AppBundle\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LevelAdmin extends BaseAdmin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('step', null, array(
                'label' => 'Seuil',
            ))
            ->add('category', null, array(
                'label' => 'Catégorie',
            ))
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
                )
            )
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Données du palier')
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('step', null, array(
                'label' => 'Seuil',
            ))
            ->add('category', null, array(
                'label' => 'Catégorie',
            ))
            ->end()
        ;
    }
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Données du palier')
            ->add('step', TextType::class, array(
                'label' => 'Seuil',
            ))
            ->add('category', ModelListType::class, array(
                'label' => 'Catégorie',
            ))
            ->end()
            ->with('Champs traductibles')
            ->add('translations', TranslationsType::class)
            ->end()
        ;
    }
}