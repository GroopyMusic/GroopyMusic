<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BlockRow
 * @package AppBundle\Entity\YB
 * @ORM\Table(name="yb_block_rows")
 * @ORM\Entity
 */
class BlockRow {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var
     * @ORM\Column(name="name", type="string", length=3)
     */
    private $name;

    /**
     * @var
     * @ORM\Column(name="nbSeats", type="integer")
     */
    private $nbSeats;

    /**
     * @var
     * @ORM\Column(name="is_seats_label_letter", type="boolean")
     */
    private $seatLetter;

    /**
     * @var Block
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Block", inversedBy="rows", cascade={"persist"})
     */
    private $block;


}