<?php
// src/XBundle/Form/ProjectsEditType.php

namespace XBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectsEditType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
  }

  public function getParent()
  {
    return ProjectsType::class;
  }
}
