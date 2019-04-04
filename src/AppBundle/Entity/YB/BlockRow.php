<?php

namespace AppBundle\Entity\YB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BlockRow
 * @package AppBundle\Entity\YB
 * @ORM\Table(name="yb_block_rows")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\BlockRowRepository")
 */
class BlockRow {

    public function __construct(){
        $this->seats = new ArrayCollection();
    }

    public function __toString(){
        return '' . $this->id . '-' . $this->name;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(name="name", type="string", length=3)
     */
    private $name;

    /**
     * @var Block
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Block", inversedBy="rows")
     */
    private $block;

    /**
     * @var
     * @ORM\Column(name="nb_seats", type="integer")
     */
    private $nbSeats;

    /**
     * @var
     * @ORM\Column(name="numerotation", type="string", length=10)
     */
    private $numerotationSystem;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Seat", mappedBy="row", cascade={"all"})
     */
    private $seats;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @param Block $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * @return mixed
     */
    public function getSeats()
    {
        return $this->seats;
    }

    public function addSeat(Seat $seat){
        $this->seats->add($seat);
    }

    public function removeSeat(Seat $seat){
        $this->seats->removeElement($seat);
    }

    /**
     * @return mixed
     */
    public function getNbSeats()
    {
        return $this->nbSeats;
    }

    /**
     * @param mixed $nbSeats
     */
    public function setNbSeats($nbSeats)
    {
        $this->nbSeats = $nbSeats;
    }

    /**
     * @return mixed
     */
    public function getNumerotationSystem()
    {
        return $this->numerotationSystem;
    }

    /**
     * @param mixed $numerotationSystem
     */
    public function setNumerotationSystem($numerotationSystem)
    {
        $this->numerotationSystem = $numerotationSystem;
    }

}