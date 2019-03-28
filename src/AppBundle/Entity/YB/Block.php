<?php

namespace AppBundle\Entity\YB;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Block
 * @package AppBundle\Entity\YB
 * @ORM\Table(name="yb_blocks")
 * @ORM\Entity
 */
class Block {

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
     * @ORM\Column(name="name", type="string", length=20)
     */
    private $name;

    /**
     * @var
     * @ORM\Column(name="type", type="string", length=15)
     */
    private $type;

    /**
     * @var
     * @ORM\Column(name="capacity", type="integer")
     */
    private $capacity;

    /**
     * @var
     * @ORM\Column(name="free_seating", type="boolean")
     */
    private $freeSeating;

    /**
     * @var BlockRow
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\BlockRow", mappedBy="block", cascade={"persist"})
     */
    private $rows;

    /**
     * @var VenueConfig
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\VenueConfig", inversedBy="blocks", cascade={"persist"})
     */
    private $config;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param mixed $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    /**
     * @return mixed
     */
    public function getFreeSeating()
    {
        return $this->freeSeating;
    }

    /**
     * @param mixed $freeSeating
     */
    public function setFreeSeating($freeSeating)
    {
        $this->freeSeating = $freeSeating;
    }

    /**
     * @return BlockRow
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param BlockRow $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return VenueConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param VenueConfig $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
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



}