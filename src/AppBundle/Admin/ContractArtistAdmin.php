<?php

namespace AppBundle\Admin;


class ContractArtistAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }

}