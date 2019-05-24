<?php
/**
 * Created by PhpStorm.
 * User: s_u_y_s_a
 * Date: 2019-05-22
 * Time: 12:00
 */

namespace Tests\AppBundle\Entity\YB;


use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;

class YBContractArtistTest extends \PHPUnit_Framework_TestCase
{
    /** @var YBContractArtist */
    private $campaign;
    /** @var EntityManagerInterface */
    private $em;
    /** @var TicketRepository */
    private $ticketRepo;
    /** @var CounterPart */
    private $cp1, $cp2;

    protected function setUp()
    {
        $ticket1 = $this->createMock(Ticket::class);
        $ticket2 = $this->createMock(Ticket::class);
        $ticket3 = $this->createMock(Ticket::class);
        $ticket4 = $this->createMock(Ticket::class);
        $ticket5 = $this->createMock(Ticket::class);
        $tickets = array($ticket1, $ticket2, $ticket3, $ticket4, $ticket5);
        $this->ticketRepo = $this->createMock(TicketRepository::class);
        $this->ticketRepo->method('getTicketsFromEvent')->willReturn($tickets);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->willReturn($this->ticketRepo);

        $this->campaign = new YBContractArtist();

        $this->cp1 = new CounterPart();
        $this->cp1->setMaximumAmount(10);
        $this->cp2 = new CounterPart();
        $this->cp2->setMaximumAmount(5);

        $this->campaign->addCounterPart($this->cp1);
        $this->campaign->addCounterPart($this->cp2);
    }

    public function testIsSoldOutTicket(){
        $available = $this->campaign->isSoldOutTicket($this->em);
        self::assertEquals(5, $available);
        $this->cp1->setMaximumAmount(7);
        $this->cp2->setMaximumAmount(5);
        $available = $this->campaign->isSoldOutTicket($this->em);
        self::assertEquals(2, $available);
    }

    public function testIsOutOfStock(){
        // TODO
    }

    protected function tearDown()
    {
        unset($this->campaign);
    }
}