<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * XTransactionalMessage
 *
 * @ORM\Table(name="x_transactional_message")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XTransactionalMessageRepository")
 */
class XTransactionalMessage
{

    public function __construct(Project $project)
    {
        $this->date = new \DateTime();
        $this->project = $project;
        $this->toDonators = false;
        $this->toBuyers = false;
    }


    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Project", inversedBy="transactionalMessages")
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="to_donators", type="boolean")
     */
    private $toDonators;

    /**
     * @var boolean
     *
     * @ORM\Column(name="to_buyers", type="boolean")
     */
    private $toBuyers;



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return XTransactionalMessage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return XTransactionalMessage
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return XTransactionalMessage
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set project
     *
     * @param \XBundle\Entity\Project $project
     *
     * @return XTransactionalMessage
     */
    public function setProject(\XBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \XBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set toDonators
     *
     * @param boolean $toDonators
     *
     * @return XTransactionalMessage
     */
    public function setToDonators($toDonators)
    {
        $this->toDonators = $toDonators;

        return $this;
    }

    /**
     * Get toDonators
     *
     * @return boolean
     */
    public function getToDonators()
    {
        return $this->toDonators;
    }

    /**
     * Set toBuyers
     *
     * @param boolean $toBuyers
     *
     * @return XTransactionalMessage
     */
    public function setToBuyers($toBuyers)
    {
        $this->toBuyers = $toBuyers;

        return $this;
    }

    /**
     * Get toBuyers
     *
     * @return boolean
     */
    public function getToBuyers()
    {
        return $this->toBuyers;
    }
}
