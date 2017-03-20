<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\UserArtist;
use AppBundle\Entity\UserFan;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 15-03-17
 * Time: 22:39
 */
class LoadUser extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        // Artist test account (credentials : artist@un-mute.be - test)
        $userA = new UserArtist();
        $userA->setUsername("artist")->setPlainPassword("test")->setEmail("artist@un-mute.be")->setFirstname("John")->setLastname("Doe")->setArtistname("Tartuffe")->setEnabled(true);
        $userA->setPhase($this->getReference('phase1'));

        $userManager->updateUser($userA, true);
        $manager->persist($userA); // persist

        // Fan test account (credentials : fan@un-mute.be - test)
        $userF = new UserFan();
        $userF->setUsername("fan")->setPlainPassword("test")->setEmail("fan@un-mute.be")->setFirstname("Elvis")->setLastname("Presley")->setEnabled(true);
        $userManager->updateUser($userF, true);
        $manager->persist($userF); // persist

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }

}
