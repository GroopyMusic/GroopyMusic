<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Genre;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGenres extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $genres_str = array("Rap", "Reggae", "Rock", "Pop", "Alternatif");

        $genres = array();

        foreach($genres_str as $gs) {
            $genre = new Genre();
            $genre->setName($gs);
            $genres[] = $genre;
            $manager->persist($genre);
            $genre->mergeNewTranslations();
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 4;
    }
}