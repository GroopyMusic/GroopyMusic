<?php

namespace AppBundle\Admin;


class PaymentAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }
}