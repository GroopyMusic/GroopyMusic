<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Province;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProvinces extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $prov_str = array("Bruxelles", "Brabant Wallon", 'Hainaut', 'Namur', 'Liège', 'Luxembourg');

        $provinces = array();

        foreach($prov_str as $ps) {
            $province = new Province();
            $province->setName($ps);
            $provinces[] = $province;
            $manager->persist($province);
            $province->mergeNewTranslations();
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}