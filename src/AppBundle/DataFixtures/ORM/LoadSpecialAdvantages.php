<?php

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
        $sa1
            ->setAvailable(true)
            ->setAvailableQuantity(3)
            ->setPriceCredits(60)
            ->setName("Visite des bureaux Un-Mute")
            ->setDescription("blablabla")
        ;

        $sa2 = new SpecialAdvantage();
        $sa2->setLocale('fr');
        $sa2
            ->setAvailable(true)
            ->setAvailableQuantity(4)
            ->setPriceCredits(25)
            
            ->setName("T-shirt Un-Mute")
            ->setDescription("blablabla")
        ;

        $manager->persist($sa1);+
        $sa1->mergeNewTranslations();
        $manager->persist($sa2);
        $sa2->mergeNewTranslations();
        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 3;
    }
}