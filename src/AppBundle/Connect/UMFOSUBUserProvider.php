<?php

namespace AppBundle\Connect;

use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UMFOSUBUserProvider extends BaseClass
{
    /** @var Logger $logger */
    private $logger;
    /** @var EventDispatcher $eventDispatcher */
    private $eventDispatcher;
    /** @var Request $request */
    private $request;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setRequest(RequestStack $requestStack) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        // Careful, this "username" is actually a Facebook token
        $username = $response->getUsername();

        // This is our User class "username": the e-mail address
        $email = $response->getEmail();

        $firstname = $response->getFirstName();
        $lastname = $response->getLastName();

        // We try to find already-created user with this email but who hasn't enabled his account yet
        $user = $this->userManager->findUserBy(array('username' => $email));

        if($user !== null && !$user->isEnabled()) {
            // Delete that user; we'll create a new one.
            $this->userManager->deleteUser($user);
            $user = null;
        }

        if($user === null) {
            // User is not registered with this e-mail address, we still try to fetch a user with his facebook_id
            // This is for the case where a user has bound a facebook account to his Un-Mute account but e-mail addresses don't match
            // e.g. created an account with Facebook but then changed his Un-Mute e-mail
            // or linked a Fb account to an existing U-M account with != e-mail
            $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        }

        else {
            // Account with response e-mail already exists -> let's log that user in
            $service = $response->getResourceOwner()->getName();

            $getter = 'get'.ucfirst($service);
            $getter_id = $getter.'Id';
            $this->request->query->add(['oauth_new_user' => $user->$getter_id() == null]);

            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());

            $event = new GetResponseUserEvent($user, $this->request);
            $this->eventDispatcher->dispatch(HWIOAuthEvents::CONNECT_CONFIRMED, $event);

            return $user;
        }

        //when the user is registrating
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';

            // The user manager creates a new User object
            $user = $this->userManager->createUser();
            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());

            // Set user info, based on Facebook info
            $user->setUsername($email);
            $user->setEmail($email);
            $user->setFirstName($firstname);
            $user->setLastName($lastname);
            $user->setPassword('null');
            $user->setEnabled(true);
            $this->userManager->updateUser($user);

            $this->eventDispatcher->dispatch(HWIOAuthEvents::REGISTRATION_SUCCESS);

            return $user;
        }
        //if user exists with != email - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setter($response->getAccessToken());

        $this->eventDispatcher->dispatch(HWIOAuthEvents::CONNECT_COMPLETED);
        return $user;
    }
}