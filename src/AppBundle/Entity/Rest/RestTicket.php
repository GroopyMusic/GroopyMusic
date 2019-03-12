<?php

namespace AppBundle\Entity\Rest;

class RestTicket {

    private $buyer;
    private $ticket_type;
    private $seat_type;
    private $barcode;
    private $error;

    public function __construct($buyer, $ticket_type, $seat_type, $barcode, $error){
        $this->buyer = $buyer;
        $this->ticket_type = $ticket_type;
        $this->seat_type = $seat_type;
        $this->barcode = $barcode;
        $this->error = $error;
    }

    public function getBuyer(){
        return $this->buyer;
    }

    public function getTicketType(){
        return $this->ticket_type;
    }

    public function getSeatType(){
        return $this->seat_type;
    }

    public function getBarcode(){
        return $this->barcode;
    }

    public function getError(){
        return $this->error;
    }

}