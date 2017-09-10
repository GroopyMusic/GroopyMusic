<?php

namespace AppBundle\Services;

use AppBundle\Entity\Hall;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class HallPhotoUploadNamer implements NamerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * var EntityManager
     */
    private $em;

    /**
     * var RequestStack
     */
    private $requestStack;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function name(FileInterface $file)
    {
        $request = $this->requestStack->getCurrentRequest();
        $hall = $this->em->getRepository('AppBundle:Hall')->find($request->get('hall'));
        return sprintf('%s.%s', 'hg-' . $hall->getSafename() . '-' . uniqid(), $file->getExtension());
    }
}