<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use \FOS\UserBundle\Model\UserManager as BaseUserManager;


class UserManager extends BaseUserManager
{
    protected $em;
    protected $repository;
    private $passwordUpdater;
    private $canonicalFieldsUpdater;
    protected $yb;

    public function __construct(EntityManagerInterface $em, PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('AppBundle:User');
        $this->passwordUpdater = $passwordUpdater;
        $this->canonicalFieldsUpdater = $canonicalFieldsUpdater;
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater);
    }

    public function setYB($yb) {
        $this->yb = $yb;
        $this->repository->setYB($yb);
    }

    public function deleteUser(UserInterface $user)
    {
        $this->em->remove($user);
    }

    public function findUserByEmail($email)
    {
        return $this->findUserBy(['username' => $email]);
    }

    public function findUserBy(array $criteria)
    {
        return $this->repository->setYB($this->yb)->findOneBy($criteria);
    }

    public function findUsers()
    {
        return $this->repository->setYB($this->yb)->findAll();
    }

    public function getClass()
    {
        $metadata = $this->em->getClassMetadata(User::class);
        return $metadata->getName();
    }

    public function reloadUser(UserInterface $user)
    {
        $this->em->refresh($user);
    }

    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->em->persist($user);
        if ($andFlush) {
            $this->em->flush();
        }
    }
}