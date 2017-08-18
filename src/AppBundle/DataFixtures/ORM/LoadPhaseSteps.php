<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 15-03-17
 * Time: 23:03
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CounterPart;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Phase;
use AppBundle\Entity\Step;
use AppBundle\Entity\StepType;

class LoadPhaseSteps extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Phase 1
        $phase1 = new Phase();
        $phase1->setLocale('fr');
        $phase1->setName("Initiation")->setNum(1);

        $step11 = new Step();
        $step11->setLocale('fr');
        $step11->setNum(1)->setName("Concert 1")->setDeadlineDuration(30)->setRequiredAmount(300)->setDescription("Blablabla");
        $step12 = new Step();
        $step12->setLocale('fr');
        $step12->setNum(2)->setName("Concert 2")->setDeadlineDuration(35)->setRequiredAmount(500)->setDescription("Blablabla");

        $phase1->addStep($step11);
        $phase1->addStep($step12);

        // Phase 2
        $phase2 = new Phase();
        $phase2->setLocale('fr');
        $phase2->setName("Expertise")->setNum(2);

        $step21 = new Step();
        $step21->setLocale('fr');
        $step21->setNum(1)->setName("Concert 3")->setDeadlineDuration(40)->setRequiredAmount(800)->setDescription("Blablabla");
        $step22 = new Step();
        $step22->setLocale('fr');
        $step22->setNum(2)->setName("Concert 4")->setDeadlineDuration(50)->setRequiredAmount(1000)->setDescription("Blablabla");

        $phase2->addStep($step21);
        $phase2->addStep($step22);

        // Step type : for example "Concert"
        $st1 = new StepType();
        $st1->setLocale('fr');
        $st1->setName("Concert")->setDescription("blablabla");
        $st1->addStep($step11)->addStep($step12)->addStep($step21)->addStep($step22);

        // Counterparts
        $cp11 = new CounterPart();
        $cp11->setLocale('fr');
        $cp11->setStep($step11)
             ->setDescription("Description")
             ->setName("Place de concert normale")
             ->setPrice(10)
            ->setMaximumAmount(50)
        ;

        $cp12 = new CounterPart();
        $cp12->setLocale('fr');
        $cp12->setStep($step11)
            ->setDescription("Description")
            ->setName("Place de concert VIP")
            ->setPrice(15)
            ->setMaximumAmount(10)
        ;

        $cp21 = new CounterPart();
        $cp21->setLocale('fr');
        $cp21->setStep($step12)
            ->setDescription("Description")
            ->setName("Place de concert normale")
            ->setPrice(30)
            ->setMaximumAmount(100)
        ;

        $manager->persist($st1);
        $manager->persist($phase1);
        $manager->persist($phase2);

        $manager->persist($cp11);
        $manager->persist($cp12);
        $manager->persist($cp21);

        $manager->flush();

        $this->addReference('phase1', $phase1);
        $this->addReference('phase2', $phase2);

        $this->addReference('step11', $step11);
        $this->addReference('step12', $step12);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}