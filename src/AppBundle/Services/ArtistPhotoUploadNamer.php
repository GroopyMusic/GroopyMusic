<?php

namespace AppBundle\Services;

use AppBundle\Entity\Artist;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ArtistPhotoUploadNamer implements NamerInterface
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

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, RequestStack $requestStack, TokenStorageInterface $token_storage)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->token_storage = $token_storage;
    }

    public function name(FileInterface $file)
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->token_storage->getToken()->getUser();

        $artist = $this->em->getRepository('AppBundle:Artist')->find($request->get('artist'));

        if(!$user->owns($artist)) {
            $this->logger->critical("You (user $user->getId()) don't own this artist ($artist->getId().");
            throw new AccessDeniedException("You don't own this artist.");
        }

        return sprintf('%s.%s', 'ag-' . $artist->getSlug() . '-' . uniqid(), $file->getExtension());
    }
}