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

class YBContractArtistPhotoUploadNamer implements NamerInterface
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

        $user = $this->token_storage->getToken()->getUser();


        $campaign = $this->em->getRepository('AppBundle:YB\YBContractArtist')->find($request->get('campaign'));

        if(!$user->ownsYBCampaign($campaign)) {
            $this->logger->critical("You (user $user->getId()) don't own this campaign ($campaign->getId().");
            throw new AccessDeniedException("You don't own this campaign.");
        }

        return sprintf('%s.%s', $campaign->getId() . '-' . $this->string_helper->slugify($campaign->getTitle()) . '-' . uniqid(), $file->getExtension());
    }
}