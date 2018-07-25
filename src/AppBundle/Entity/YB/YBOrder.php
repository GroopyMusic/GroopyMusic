<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Cart;
use AppBundle\Entity\PhysicalPersonInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="yb_order")
 **/
class YBOrder implements PhysicalPersonInterface
{
    public function getDisplayName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @var Cart
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Cart", inversedBy="yb_order")
     */
    private $cart;

    /**
     * @ORM\Column(name="last_name", type="string", length=50)
     */
    private $last_name;

    /**
     * @ORM\Column(name="first_name", type="string", length=50)
     */
    private $first_name;

    /**
     * @ORM\Column(name="email", type="string", length=60)
     */
    private $email;
}