<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\TranslationBundle\Filter\TranslationFieldFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class HallAdmin extends PartnerAdmin  {

    public function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);
        $listMapper
            ->remove('type')
            ->remove('_action')
            ->add('step', null, array(
                'label' => 'Palier',
            ))
            ->add('_action', null, array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                    )
                )
            )
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        parent::configureShowFields($showMapper);

        $showMapper
            ->with('Données de la salle')
                ->add('step', 'sonata_type_model', array(
                    'label' => 'Palier correspondant',
                ))
                ->add('province', 'sonata_type_model', array(
                    'label' => 'Province',
                ))
                ->add('capacity', null, array(
                    'label' => 'Capacité (en nombre de personnes)',
                ))
                ->add('delay', null, array(
                    'label' => 'Délai demandé (en jours)',
                ))
                ->add('price', null, array(
                    'label' => 'Prix demandé',
                ))
                ->add('cron_automatic_days_formatted', null, array(
                    'label' => 'Jours automatiques',
                ))
                ->add('available_dates_string', 'text', array(
                    'label' => 'Dates disponibles (calculé automatiquement mais modifiable)',
                ))
                ->add('technical_specs', null, array(
                    'label' => 'Spécifications techniques (PDF)',
                    'template' => 'AppBundle:Admin/Hall:technical_specs.html.twig',
                ))
                ->add('photos', null, array(
                    'label' => 'Photos',
                    'template' => 'AppBundle:Admin/Hall:photos.html.twig',
                ))
            ->end()
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        parent::configureFormFields($form);
        $container = $this->getConfigurationPool()->getContainer();

        $form
            ->with('Données de la salle')
                ->add('step', 'sonata_type_model', array(
                    'label' => 'Palier correspondant',
                    'required' => true,
                    'btn_add' => false,
                ))
                ->add('province', 'sonata_type_model', array(
                    'label' => 'Province',
                    'required' => true,
                    'btn_add' => false,
                ))
                ->add('capacity', null, array(
                    'label' => 'Capacité (en nombre de personnes)',
                    'required' => true,
                ))
                ->add('delay', null, array(
                    'label' => 'Délai demandé (en jours)',
                    'required' => true,
                ))
                ->add('price', null, array(
                    'label' => 'Prix demandé',
                    'required' => true,
                ))
                ->add('cron_automatic_days', null, array(
                    'required' => false,
                    'label' => 'Jours automatiques',
                    'entry_type' => 'checkbox',
                    'entry_options' => [
                        'required' => false,
                    ]
                ))
                ->add('available_dates_string', 'text', array(
                    'label' => 'Dates disponibles (calculé automatiquement mais modifiable)',
                    'empty_data' => '',
                    'required' => false,
                    'attr' => ['class' => 'multiDatesPicker'],
                ))

                ->add('technical_specs_file', VichFileType::class, array(
                    'required' => false,
                    'allow_delete' => true,
                    'label' => 'Spécifications techniques (PDF)',
                    'download_uri' => function(Hall $hall) use ($container) {
                        return $container->get('assets.packages')->getUrl($container->get('vich_uploader.templating.helper.uploader_helper')->asset($hall, 'technical_specs_file'));
                    },
                    'download_label' => 'Télécharger',
                ))
                ->add('dummyForm', 'text', array(
                    'label' => 'Photos',
                ))
            ->end()
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