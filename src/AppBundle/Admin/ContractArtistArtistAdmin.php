<?php

namespace AppBundle\Admin;

use AppBundle\Entity\StepType;
use AppBundle\Form\ConcertPossibilityType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

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