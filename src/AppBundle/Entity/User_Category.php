<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 11:14
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User_Category
 *
 * @ORM\Table(name="fos_user__category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User_CategoryRepository")
 */
class User_Category
{

    public function __construct()
    {
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
     * @var int
     *
     * @ORM\Column(name="statistic", type="integer")
     */
    private $statistic;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="category_statistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="user_statistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Level", inversedBy="statistics")
     */
    private $level;

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
     * Set statistic
     *
     * @param integer $statistic
     *
     * @return User_Category
     */
    public function setStatistic($statistic)
    {
        $this->statistic = $statistic;

        return $this;
    }

    /**
     * Get statistic
     *
     * @return integer
     */
    public function getStatistic()
    {
        return $this->statistic;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return User_Category
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return User_Category
     */
    public function setCategory(\AppBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set level
     *
     * @param \AppBundle\Entity\Level $level
     *
     * @return User_Category
     */
    public function setLevel(\AppBundle\Entity\Level $level = null)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return \AppBundle\Entity\Level
     */
    public function getLevel()
    {
        return $this->level;
    }
}
