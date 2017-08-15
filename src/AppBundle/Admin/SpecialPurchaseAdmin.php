<?php

namespace AppBundle\Admin;


class SpecialPurchaseAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }
}