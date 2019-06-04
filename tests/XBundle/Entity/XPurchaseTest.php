<?php

namespace Tests\XBundle\Entity;

use PHPUnit\Framework\TestCase;
use XBundle\Entity\ChoiceOption;
use XBundle\Entity\XPurchase;

class XPurchaseTest extends TestCase
{

    private $purchase;
    private $choice1;
    private $choice2;

    protected function setUp()
    {
        $this->purchase = new XPurchase();
        $this->choice1 = new ChoiceOption();
        $this->choice2 = new ChoiceOption();
    }

    protected function tearsDown()
    {
        unset($this->purchase);
    }


    public function testHasChoice1()
    {
        $this->purchase->addChoice($this->choice1);
        $this->assertTrue($this->purchase->hasChoices($this->choice1));
    }


    public function testHasChoice2()
    {
        $this->purchase->addChoice($this->choice1);
        $this->purchase->addChoice($this->choice2);
        $this->assertTrue($this->purchase->hasChoices([$this->choice1, $this->choice2]));
    }


    public function testHasChoice3()
    {
        $this->purchase->addChoice($this->choice1);
        $this->purchase->addChoice($this->choice2);
        $choice3 = new ChoiceOption();
        $this->assertFalse($this->purchase->hasChoices([$this->choice1, $choice3]));
    }

}