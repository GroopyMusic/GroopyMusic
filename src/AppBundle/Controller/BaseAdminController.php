<?php

namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseAdminController extends CRUDController
{
    protected $container;
    protected $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->configure();
        $this->logger = $logger;
    }
}