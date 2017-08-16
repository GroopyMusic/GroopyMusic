<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;

class ContractFanAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }

    public function configureListFields(ListMapper $list)
    {
        $list->add('id')
            ->add('user')
            ->add('contractArtist')
        ;
    }
}