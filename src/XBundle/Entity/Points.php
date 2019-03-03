<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Points
 *
 * @ORM\Table(name="points")
 * @ORM\Entity(repositoryClass="XBundle\Repository\PointsRepository")
 */
class Points
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user_id; 

    /**
     * @ORM\ManyToOne(targetEntity="\XBundle\Entity\Projects")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     */
    private $project_id; 

    /**
     * @var bool
     *
     * @ORM\Column(name="gavePoints", type="boolean")
     */
    private $gavePoints;



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
     * Set projectId
     *
     * @param \XBundle\Entity\Projects $projectId
     *
     * @return Points
     */
    public function setProjectId(\XBundle\Entity\Projects $projectId)
    {
        $this->project_id = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return \XBundle\Entity\Projects
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Set gavePoints
     *
     * @param boolean $gavePoints
     *
     * @return Points
     */
    public function setGavePoints($gavePoints)
    {
        $this->gavePoints = $gavePoints;

        return $this;
    }

    /**
     * Get gavePoints
     *
     * @return boolean
     */
    public function getGavePoints()
    {
        return $this->gavePoints;
    }

    /**
     * Set userId
     *
     * @param \AppBundle\Entity\User $userId
     *
     * @return Points
     */
    public function setUserId(\AppBundle\Entity\User $userId = null)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserId()
    {
        return $this->user_id;
    }
}
