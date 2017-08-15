<?php

namespace AppBundle\Admin;


class ArtistAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }
}