<?php

namespace AppBundle\Entity\Rest;

class RestTicket {

    private $buyer;
    private $ticket_type;
    private $seat_type;
    private $barcode;
    private $error;
    private $isValidated;

    public function __construct($buyer, $ticket_type, $seat_type, $barcode, $error, $isValidated){
        $this->buyer = $buyer;
        $this->ticket_type = $ticket_type;
        $this->seat_type = $seat_type;
        $this->barcode = $barcode;
        $this->error = $error;
        $this->isValidated = $isValidated;
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

    public function isValidated(){
        return $this->isValidated;
    }

}