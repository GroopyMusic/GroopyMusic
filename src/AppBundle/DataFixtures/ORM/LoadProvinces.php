<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Address;
use AppBundle\Entity\Hall;
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

        // Halls
        $address = new Address();
        $address->setCity('Brussels')
            ->setCountry('Belgium')
            ->setNumber(20)
            ->setStreet('Rue de la Fontaine Dieu')
            ->setZipcode('5310');
        $hall1 = new Hall();
        $hall1->setName('Salle2')
            ->setAddress($address)
            ->setCapacity(100)
            ->setDelay(60)
            ->setPrice(300)
            ->setStep($this->getReference('step11'))
            ->setProvince($provinces[1]);

        $manager->persist($hall1);
        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}