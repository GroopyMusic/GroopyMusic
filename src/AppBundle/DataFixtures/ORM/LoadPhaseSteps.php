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
use AppBundle\Entity\Phase;
use AppBundle\Entity\Step;
use AppBundle\Entity\StepType;

class LoadPhaseSteps extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Phase 1
        $phase1 = new Phase();
        $phase1->setName("Initiation")->setNum(1);

        $step11 = new Step(); $step11->setNum(1)->setName("Concert 1")->setDeadlineDuration(30);
        $step12 = new Step(); $step12->setNum(2)->setName("Concert 2")->setDeadlineDuration(35);

        $phase1->addStep($step11);
        $phase1->addStep($step12);

        // Phase 2
        $phase2 = new Phase();
        $phase2->setName("Expertise")->setNum(2);

        $step21 = new Step(); $step21->setNum(1)->setName("Concert 3")->setDeadlineDuration(40);
        $step22 = new Step(); $step22->setNum(2)->setName("Concert 4")->setDeadlineDuration(50);

        $phase2->addStep($step21);
        $phase2->addStep($step22);

        // Step type : for example "Concert"
        $st1 = new StepType();
        $st1->setName("Concert")->setDescription("blablabla");
        $st1->addStep($step11)->addStep($step12)->addStep($step21)->addStep($step22);

        $manager->persist($st1);
        $manager->persist($phase1);
        $manager->persist($phase2);
        $manager->flush();

        $this->addReference('phase1', $phase1);
        $this->addReference('phase2', $phase2);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}