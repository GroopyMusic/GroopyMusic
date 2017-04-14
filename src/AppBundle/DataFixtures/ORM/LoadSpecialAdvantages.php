<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 15-03-17
 * Time: 23:03
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use AppBundle\Entity\SpecialAdvantage;

class LoadSpecialAdvantages extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $sa1 = new SpecialAdvantage();
        $sa1->setName("Visite des bureaux Un-Mute")
            ->setDescription("blablabla")
            ->setAvailable(true)
            ->setAvailableQuantity(3)
            ->setPriceCredits(60);

        $sa2 = new SpecialAdvantage();
        $sa2->setName("T-shirt Un-Mute")
            ->setDescription("blablabla")
            ->setAvailable(true)
            ->setAvailableQuantity(4)
            ->setPriceCredits(25);

        $manager->persist($sa1);
        $manager->persist($sa2);
        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 3;
    }
}