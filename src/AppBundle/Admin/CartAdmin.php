<?php

namespace AppBundle\Admin;


class CartAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }
}