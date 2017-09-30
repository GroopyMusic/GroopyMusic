<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\SuggestionTypeEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSuggestionTypes extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $type1 = new SuggestionTypeEnum();
        $type1->setLocale('fr')->setName('Demande de renseignement');
        $type1->mergeNewTranslations();

        $type2 = new SuggestionTypeEnum();
        $type2->setLocale('fr')->setName('Report de bug');
        $type2->mergeNewTranslations();

        $type3 = new SuggestionTypeEnum();
        $type3->setLocale('fr')->setName("Suggestion d'amélioration");
        $type3->mergeNewTranslations();

        $type4 = new SuggestionTypeEnum();
        $type4->setLocale('fr')->setName('Autre');
        $type4->mergeNewTranslations();

        $manager->persist($type1);
        $manager->persist($type2);
        $manager->persist($type3);
        $manager->persist($type4);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 3;
    }
}