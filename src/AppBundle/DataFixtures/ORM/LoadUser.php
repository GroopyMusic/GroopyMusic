<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
        $userA = new User();
        $userA->setUsername("artist")->setPlainPassword("test")->setEmail("artist@un-mute.be")->setFirstname("John")->setLastname("Doe")->setEnabled(true);
        $userManager->updateUser($userA, true);

        $artist = new Artist($this->getReference('phase1'));
        $artist->setLocale('fr');
        $artist->setArtistname('SeeUsoon')->setShortDescription("short")->setBiography('long');

        $artist_userA = new Artist_User();
        $artist_userA->setArtist($artist);
        $artist_userA->setUser($userA);

        $manager->persist($userA);
        $manager->persist($artist);
        $manager->persist($artist_userA);

        // Test contracts

        //$c1 = new ContractArtist();
        //$c1->setDate(new \DateTime())->setArtist($artist)->setDateEnd(new \DateTime("2018-6-30"))->setMotivations("")->setStep($this->getReference('step11'));

        //$manager->persist($c1);

        // Fan test account (credentials : fan@un-mute.be - test)
        $userF = new User();
        $userF->setUsername("fan")->setPlainPassword("test")->setEmail("fan@un-mute.be")->setFirstname("Elvis")->setLastname("Presley")->setCredits(100)->setEnabled(true);
        $userManager->updateUser($userF, true);
        $manager->persist($userF); // persist

        // Admin test account (credentials : admin@un-mute.be - test)
        $userAdmin = new User();
        $userAdmin->setUsername("admin")->setPlainPassword("test")->setEmail("admin@un-mute.be")->setFirstname("Kids")->setLastname("United")->setCredits(1000)->setEnabled(true)->addRole('ROLE_SUPER_ADMIN');
        $userManager->updateUser($userAdmin, true);
        $manager->persist($userAdmin); // persist

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }

}
