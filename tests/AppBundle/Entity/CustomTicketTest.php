<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Address;
use AppBundle\Entity\YB\CustomTicket;
use AppBundle\Entity\YB\PublicTransportStation;
use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\YBContractArtist;

class CustomTicketTest extends \PHPUnit_Framework_TestCase {

    /** @var CustomTicket $customTicket */
    private $customTicket;

    protected function setUp(){
        $adresse = $this->createMock(Address::class);
        $adresse->method('getLatitude')->willReturn(50.8288651000);
        $adresse->method('getLongitude')->willReturn(4.3860961972);

        $venue = $this->createMock(Venue::class);
        $venue->method('getAddress')->willReturn($adresse);

        $campaign = $this->createMock(YBContractArtist::class);
        $campaign->method('getVenue')->willReturn($venue);

        $this->customTicket = new CustomTicket($campaign);

        $station1 = new PublicTransportStation('Etterbeek', 50.8212080000, 4.3902220000, PublicTransportStation::SNCB, 0.898);
        $station2 = new PublicTransportStation('LA CHASSE', 50.8311770000, 4.3899450000, PublicTransportStation::STIB, 0.372);
        $station3 = new PublicTransportStation('EGLISE ST-ANTOINE', 50.8298560000, 4.3860970000, PublicTransportStation::STIB, 0.110);
        $station4 = new PublicTransportStation('RODIN', 50.8275370000, 4.3818380000, PublicTransportStation::STIB, 0.333);

        $stations = [];
        $stations[] = ($station1);
        $stations[] = ($station2);
        $stations[] = ($station3);
        $stations[] = ($station4);
        $this->customTicket->setStations($stations);
    }

    public function testGetMapQuestUrl(){
        $urlExpected = 'https://www.mapquestapi.com/staticmap/v5/map?center=50.8288651,4.3860961972&locations=50.8288651,4.3860961972|marker-red';
        $urlExpected .= '||50.821208,4.390222|marker-1-blue';
        $urlExpected .= '||50.831177,4.389945|marker-2-green';
        $urlExpected .= '||50.829856,4.386097|marker-3-green';
        $urlExpected .= '||50.827537,4.381838|marker-4-green';
        $urlExpected .= '&size=210,200&zoom=13&key=ma_cle_api';
        self::assertEquals($urlExpected, $this->customTicket->getMapQuestUrl('ma_cle_api'));
    }

    public function testGetSortedStations(){
        $sorted = $this->customTicket->getSortedStations();
        self::assertEquals("EGLISE ST-ANTOINE", $sorted[0]->getName());
        self::assertEquals("RODIN", $sorted[1]->getName());
        self::assertEquals("LA CHASSE", $sorted[2]->getName());
        self::assertEquals("Etterbeek", $sorted[3]->getName());
    }

    protected function tearDown(){
        unset($this->customTicket);
    }

}