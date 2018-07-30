<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="step_sales")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StepSalesRepository")
 */
class StepSales extends BaseStep
{

}
