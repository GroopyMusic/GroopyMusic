<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="step_pot")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StepPotRepository")
 */
class StepPot extends BaseStep
{

}
