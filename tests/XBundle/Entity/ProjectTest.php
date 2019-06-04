<?php

namespace Tests\XBundle\Entity;

use PHPUnit\Framework\TestCase;
use XBundle\Entity\ChoiceOption;
use XBundle\Entity\Project;
use XBundle\Entity\Product;
use XBundle\Entity\XCart;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XOrder;
use XBundle\Entity\XPayment;
use XBundle\Entity\XPurchase;


class ProjectTest extends TestCase
{

    private $project;
    private $product1;
    private $product2;
    private $choice1;
    private $choice2;
    private $choice3;

    protected function setUp()
    {
        $this->project = new Project();
        $this->project->setDateEnd("2019-05-20 15:00:00");
        $this->project->setDateValidation("2019-05-18 15:00:00");

        $this->product1 = new Product();
        $this->product2 = new Product();

        $this->choice1 = new ChoiceOption();
        $this->choice2 = new ChoiceOption();
        $this->choice3 = new ChoiceOption();

        $cart1 = new XCart();
        $cart1->setPaid(true);
        $cf1 = new XContractFan($this->project);
        $cf1->setCart($cart1);
        $purchase1 = new XPurchase();
        $purchase1->setContractFan($cf1);
        $purchase1->setProduct($this->product1);
        $purchase1->setQuantity(2);
        $purchase1->addChoice($this->choice1);
        $purchase1->addChoice($this->choice2);
        $cf1->addPurchase($purchase1);
        $order1 = new XOrder();
        $order1->setEmail("buyer1@email.com");
        $order1->setCart($cart1);
        $pay1 = new XPayment();
        $pay1->setDate("2019-05-10 15:00:00");
        $pay1->setCart($cart1);
        $cart1->setPayment($pay1);
        $cart1->setOrder($order1);
        $cart1->addContract($cf1);
        $this->project->addContribution($cf1);

        $cart2 = new XCart();
        $cart2->setPaid(true);
        $cf2 = new XContractFan($this->project);
        $cf2->setCart($cart2);
        $cf2->setIsDonation(true);
        $order2 = new XOrder();
        $order2->setEmail("donator1@email.com");
        $order2->setCart($cart2);
        $pay2 = new XPayment();
        $pay2->setDate("2019-05-14 15:00:00");
        $pay2->setCart($cart2);
        $cart2->setPayment($pay2);
        $cart2->setOrder($order2);
        $cart2->addContract($cf2);
        $this->project->addContribution($cf2);

        $cart3 = new XCart();
        $cart3->setPaid(true);
        $cf3 = new XContractFan($this->project);
        $cf3->setCart($cart3);
        $purchase2 = new XPurchase();
        $purchase2->setContractFan($cf2);
        $purchase2->setProduct($this->product2);
        $cf3->addPurchase($purchase2);
        $order3 = new XOrder();
        $order3->setEmail("buyer1@email.com");
        $order3->setCart($cart3);
        $pay3 = new XPayment();
        $pay3->setDate("2019-05-18 15:00:00");
        $pay3->setCart($cart3);
        $cart3->setPayment($pay3);
        $cart3->setOrder($order3);
        $cart3->addContract($cf3);
        $this->project->addContribution($cf3);

        $cart4 = new XCart();
        $cart4->setPaid(true);
        $cf4 = new XContractFan($this->project);
        $cf4->setCart($cart4);
        $purchase3 = new XPurchase();
        $purchase3->setContractFan($cf4);
        $purchase3->setProduct($this->product1);
        $purchase3->setQuantity(1);
        $purchase3->addChoice($this->choice1);
        $purchase3->addChoice($this->choice3);
        $cf4->addPurchase($purchase3);
        $order4 = new XOrder();
        $order4->setEmail("buyer2@email.com");
        $order4->setCart($cart4);
        $pay4 = new XPayment();
        $pay4->setDate("2019-05-19 15:00:00");
        $pay4->setCart($cart4);
        $cart4->setPayment($pay4);
        $cart4->setOrder($order4);
        $cart4->addContract($cf4);
        $this->project->addContribution($cf4);

        $cart5 = new XCart();
        $cart5->setPaid(true);
        $cf5 = new XContractFan($this->project);
        $cf5->setCart($cart5);
        $cf5->setIsDonation(true);
        $order5 = new XOrder();
        $order5->setEmail("donator2@gmail.com");
        $order5->setCart($cart5);
        $pay5 = new XPayment();
        $pay5->setDate("2019-05-20 10:00:00");
        $pay5->setCart($cart5);
        $cart5->setPayment($pay5);
        $cart5->setOrder($order5);
        $cart5->addContract($cf5);
        $this->project->addContribution($cf5);
    }

    protected function tearDown()
    {
        unset($this->project);
        unset($this->product1);
        unset($this->product2);
        unset($this->choice1);
        unset($this->choice2);
        unset($this->choice3);
    }

    
    public function testHasValidatedProducts1()
    {
        $product = new Product();
        $product->setProject($this->project);
        $product->setValidated(true);
        $this->project->addProduct($product);

        $this->assertTrue($this->project->hasValidatedProducts());
    }


    public function testHasValidatedProducts2()
    {
        $product = new Product($this->project);
        $product->setProject($this->project);
        $this->project->addProduct($product);

        $this->assertNotTrue($this->project->hasValidatedProducts());
    }


    public function testGetNbDonations()
    {
        $this->assertEquals(2, $this->project->getNbDonations());
    }

    
    public function testGetNbSales()
    {
        $this->assertEquals(3, $this->project->getNbSales());
    }


    public function testGetNbContributors()
    {
        $this->assertEquals(4, $this->project->getNbContributors());
    }


    public function testGetDonationsPaid()
    {
        $this->assertCount(2, $this->project->getDonationsPaid());
    }


    public function testGetDonators1()
    {
        $this->assertCount(2, $this->project->getDonators());
    }


    public function testGetDonators2()
    {
        $this->assertCount(1, $this->project->getDonators(true));
    }


    public function testProductSalesPaid1()
    {
        $this->assertCount(2, $this->project->getProductSalesPaid([$this->product1]));
    }

    public function testProductSalesPaid2()
    {
        $this->assertCount(3, $this->project->getProductSalesPaid([$this->product1, $this->product2]));
    }

    public function testProductSalesPaid3()
    {
        $product3 = new Product();
        $this->assertCount(0, $this->project->getProductSalesPaid([$product3]));
    }


    public function testGetSalesPaid()
    {
        $this->assertCount(3, $this->project->getSalesPaid());
    }


    public function testGetBuyers1()
    {
        $this->assertCount(3, $this->project->getBuyers());
    }

 
    public function testGetBuyers2()
    {
        $this->assertCount(2, $this->project->getBuyers(true));
    }


    public function testGetBuyers3()
    {
        $this->assertCount(2, $this->project->getBuyers(false, [$this->product1]));
    }


    public function testGetBuyers4()
    {
        $this->assertCount(1, $this->project->getBuyers(true, [$this->product1]));
    }
    

    public function testGetContributionsPaid()
    {
        $this->assertCount(5, $this->project->getContributionsPaid());
    }


    public function testGetContributors1()
    {
        $this->assertCount(3, $this->project->getContributors(true));
    }


    public function testGetContributors2()
    {
        $this->assertCount(5, $this->project->getContributors());
    }


    public function testWideContributors()
    {
        $this->assertCount(5, $this->project->getWideContributors());
    }


    public function testGetNbPerChoice1()
    {
        $this->assertEquals(2, $this->project->getNbPerChoice($this->product1, [$this->choice1, $this->choice2]));
    }


    public function testGetNbPerChoice2()
    {
        $this->assertEquals(1, $this->project->getNbPerChoice($this->product1, [$this->choice1, $this->choice3]));
    }


    public function testGetNbPerChoice3()
    {
        $this->assertEquals(0, $this->project->getNbPerChoice($this->product1, [$this->choice2, $this->choice3]));
    }

}