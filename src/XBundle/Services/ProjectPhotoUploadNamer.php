<?php

namespace XBundle\Services;

use AppBundle\Services\StringHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProjectPhotoUploadNamer implements NamerInterface
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

    /**
     * @var TokenStorage
     */
    private $token_storage;

    private $string_helper;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, RequestStack $requestStack, TokenStorageInterface $token_storage, StringHelper $stringHelper)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->token_storage = $token_storage;
        $this->string_helper = $stringHelper;
    }

    public function name(FileInterface $file)
    {
        $request = $this->requestStack->getCurrentRequest();
        
        $project = $this->em->getRepository('XBundle:Project')->find($request->get('project'));

        $code = $request->get('code');

        if($project->getCode() != $code) {
            $this->logger->critical("Invalid code for editing project ($project->getId().");
            throw new AccessDeniedException("You don't own this project.");
        }

        return sprintf('%s.%s', $project->getId() . '-' . $this->string_helper->slugify($project->getTitle()) . '-' . uniqid(), $file->getExtension());
    }
}