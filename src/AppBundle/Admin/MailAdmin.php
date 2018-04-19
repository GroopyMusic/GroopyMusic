<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class MailAdmin extends BaseAdmin
{
    protected $baseRoutePattern = 'mail';
    protected $baseRouteName = 'mail';

    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->add('sendEmail', 'sendEmail')
            ->add('getMembers', 'getMembers')
            ->add('getUserParticipants', 'getUserParticipants')
            ->add('getArtistParticipants', 'getArtistParticipants')
            ->add('getSummary', 'getSummary')
            ->add('sendEmail', 'sendEmail');
    }
}