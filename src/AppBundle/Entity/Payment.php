<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 09-04-17
 * Time: 15:56
 */

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Payment
{
    /**
     * @Assert\NotBlank(message="payment.accept_conditions.notblank")
     */
    private $accept_conditions;


    public function getAcceptConditions()
    {
        return $this->accept_conditions;
    }

    public function setAcceptConditions($accept_conditions)
    {
        $this->accept_conditions = $accept_conditions;
    }
}