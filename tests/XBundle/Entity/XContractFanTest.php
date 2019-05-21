<?php

namespace Tests\XBundle\Entity;

use PHPUnit\Framework\TestCase;
use XBundle\Entity\Product;
use XBundle\Entity\XCart;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XPurchase;

class XContractFanTest extends TestCase
{

    private $contractFan;
    //private $product1;

    protected function setUp()
    {
        $this->contractFan = new XContractFan(new \XBundle\Entity\Project);

        $product1 = new Product();
        $product2 = new Product();
        $product2->setIsTicket(true);

        $purchase1 = new XPurchase();
        $purchase1->setContractFan($this->contractFan);
        $purchase1->setProduct($product1);

        $purchase2 = new XPurchase();
        $purchase2->setContractFan($this->contractFan);
        $purchase2->setProduct($product2);

        $this->contractFan->addPurchase($purchase1);
        $this->contractFan->addPurchase($purchase2);
        
    }

    protected function tearsDown()
    {
        unset($this->contractFan);
    }

    public function testTicketsPurchases()
    {
        $this->assertCount(1, $this->contractFan->getTicketsPurchases());
    }

}