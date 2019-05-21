<?php

namespace Tests\XBundle\Entity;

use PHPUnit\Framework\TestCase;
use XBundle\Entity\Project;
use XBundle\Entity\Product;
use XBundle\Entity\XCart;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XOrder;


class ProjectTest extends TestCase
{

    private $project;

    protected function setUp()
    {
        $this->project = new Project();

        $cart1 = new XCart();
        $cart1->setPaid(true);
        $cf1 = new XContractFan($this->project);
        $cf1->setCart($cart1);
        $order1 = new XOrder();
        $order1->setEmail("contributor1@email.com");
        $order1->setCart($cart1);
        $cart1->setOrder($order1);
        $cart1->addContract($cf1);
        $this->project->addContribution($cf1);

        $cart2 = new XCart();
        $cart2->setPaid(true);
        $cf2 = new XContractFan($this->project);
        $cf2->setCart($cart2);
        $cf2->setIsDonation(true);
        $order2 = new XOrder();
        $order2->setEmail("donator@email.com");
        $order2->setCart($cart2);
        $cart2->setOrder($order2);
        $cart2->addContract($cf2);
        $this->project->addContribution($cf2);

        $cart3 = new XCart();
        $cart3->setPaid(true);
        $cf3 = new XContractFan($this->project);
        $cf3->setCart($cart3);
        $order3 = new XOrder();
        $order3->setEmail("contributor2@email.com");
        $order3->setCart($cart3);
        $cart3->setOrder($order3);
        $cart3->addContract($cf3);
        $this->project->addContribution($cf3);

        $cart4 = new XCart();
        $cart4->setPaid(true);
        $cf4 = new XContractFan($this->project);
        $cf4->setCart($cart4);
        $order4 = new XOrder();
        $order4->setEmail("contributor1@email.com");
        $order4->setCart($cart4);
        $cart4->setOrder($order4);
        $cart4->addContract($cf4);
        $this->project->addContribution($cf4);
    }

    protected function tearDown()
    {
        unset($this->project);
    }


    /**
     * Success: Count contributors number; only once a contributor who has paid several times
     */
    public function testGetNbContributors()
    {
        $this->assertEquals(3, $this->project->getNbContributors());
    }

    /**
     * Success: Retrieve project donators
     */
    public function testGetDonators()
    {
        $this->assertCount(1, $this->project->getDonators());
    }

    /**
     * Success: Retrieve project buyers
     */
    public function testGetBuyers()
    {
        $this->assertCount(3, $this->project->getBuyers());
    }

    /**
     * Success: Check if project has validated products
     * Return: true
     */
    public function testHasValidatedProducts1()
    {
        $product = new Product();
        $product->setProject($this->project);
        $product->setValidated(true);
        $this->project->addProduct($product);

        $this->assertTrue($this->project->hasValidatedProducts());
    }

    /**
     * Success: Check if project has validated products
     * Return: false
     */
    public function testHasValidatedProducts2()
    {
        $product = new Product($this->project);
        $product->setProject($this->project);
        $this->project->addProduct($product);

        $this->assertNotTrue($this->project->hasValidatedProducts());
    }



}