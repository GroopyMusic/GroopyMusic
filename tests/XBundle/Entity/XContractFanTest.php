<?php

namespace Tests\XBundle\Entity;

use PHPUnit\Framework\TestCase;
use XBundle\Entity\ChoiceOption;
use XBundle\Entity\Product;
use XBundle\Entity\XCart;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XPurchase;

class XContractFanTest extends TestCase
{

    private $contractFan;
    private $product1;
    private $product2;
    private $purchase1;
    private $purchase2;
    private $choice1;
    private $choice2;

    protected function setUp()
    {
        $this->contractFan = new XContractFan(new \XBundle\Entity\Project);

        $this->product1 = new Product();
        $this->product2 = new Product();
        $this->product2->setIsTicket(true);

        $this->choice1 = new ChoiceOption();
        $this->choice2 = new ChoiceOption();

        $this->purchase1 = new XPurchase();
        $this->purchase1->setContractFan($this->contractFan);
        $this->purchase1->setProduct($this->product1);
        $this->purchase1->setQuantity(1);
        $this->purchase1->addChoice($this->choice1);
        $this->purchase1->addChoice($this->choice2);

        $this->purchase2 = new XPurchase();
        $this->purchase2->setContractFan($this->contractFan);
        $this->purchase2->setProduct($this->product2);
        $this->purchase2->setQuantity(2);

        $this->contractFan->addPurchase($this->purchase1);
        $this->contractFan->addPurchase($this->purchase2);
        
    }

    protected function tearsDown()
    {
        unset($this->contractFan);
        unset($this->product1);
        unset($this->product2);
        unset($this->purchase1);
        unset($this->purchase2);
        unset($this->choice1);
        unset($this->choice2);
    }


    public function testProductsQuantity()
    {
        $this->assertEquals(3, $this->contractFan->getProductsQuantity());
    }

 
    public function testTicketsPurchases()
    {
        $this->assertCount(1, $this->contractFan->getTicketsPurchases());
    }

 
    public function testGetPurchasesForProduct1()
    {
        $this->assertCount(1, $this->contractFan->getPurchasesForProduct([$this->product1]));
    }


    public function testGetPurchasesForProduct2()
    {
        $this->assertCount(2, $this->contractFan->getPurchasesForProduct([$this->product1, $this->product2]));
    }


    public function testGetPurchasesForProduct3()
    {
        $product3 = new Product();
        $this->assertNull($this->contractFan->getPurchasesForProduct([$product3]));
    }

    
    public function testGetPurchaseForProductWithChoices1()
    {
        $this->assertSame($this->purchase1, $this->contractFan->getPurchaseForProductWithChoices($this->product1, [$this->choice1, $this->choice2]));
    }


    public function testGetPurchaseForProductWithChoices2()
    {
        $choice3 = new ChoiceOption();
        $this->assertNull($this->contractFan->getPurchaseForProductWithChoices($this->product1, [$this->choice1, $choice3]));
    }

}