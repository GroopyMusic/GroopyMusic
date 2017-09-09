<?php

namespace AppBundle\Services;

use AppBundle\Entity\Artist;
use Doctrine\ORM\EntityManager;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

class DropzoneUploadNamer implements NamerInterface
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

    public function __construct(LoggerInterface $logger, EntityManager $em, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function name(FileInterface $file)
    {
        $request = $this->requestStack->getCurrentRequest();
        $artist = $this->em->getRepository('AppBundle:Artist')->find($request->get('artist'));
        return sprintf('%s.%s', $artist->getSafename() . '-' . uniqid(), $file->getExtension());
    }
}