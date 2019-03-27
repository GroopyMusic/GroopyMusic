<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;

/**
 * YBCommission
 *
 * @ORM\Table(name="yb_commission")
 * @ORM\Entity
 */
class YBCommission /*implements \ArrayAccess*/
{
    public function __construct()
    {
        $this->minimumThreshold = 0;
        $this->fixedAmount = 0;
        $this->percentageAmount = 0;
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\YBContractArtist", inversedBy="commissions")
     */
    private $campaign;

    /**
     * @var float
     * @ORM\Column(name="minimum_fixed_amount", type="float")
     */
    private $minimumThreshold;

    /**
     * @var float
     * @ORM\Column(name="fixed_amount", type="float")
     */
    private $fixedAmount;

    /**
     * @var float
     * @ORM\Column(name="variable_amount", type="float")
     */
    private $percentageAmount;

    /**
     * @return float
     */
    public function getMinimumThreshold()
    {
        return $this->minimumThreshold;
    }

    /**
     * @param float $minimumThreshold
     * @return YBCommission
     */
    public function setMinimumThreshold($minimumThreshold)
    {
        $this->minimumThreshold = $minimumThreshold;
        return $this;
    }

    /**
     * @return float
     */
    public function getFixedAmount()
    {
        return $this->fixedAmount;
    }

    /**
     * @param float $fixedAmount
     */
    public function setFixedAmount($fixedAmount)
    {
        $this->fixedAmount = $fixedAmount;
    }

    /**
     * @return float
     */
    public function getPercentageAmount()
    {
        return $this->percentageAmount;
    }

    /**
     * @param float $percentageAmount
     * @return YBCommission
     */
    public function setPercentageAmount($percentageAmount)
    {
        if ($percentageAmount < 0){
            $percentageAmount = 0;
        }
        if ($percentageAmount > 100){
            $percentageAmount = 100;
        }
        $this->percentageAmount = $percentageAmount;
        return $this;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return (in_array($offset, ["minimumThreshold", "fixedAmount", "percentageAmount"]));
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
    }

}