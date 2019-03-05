<?php

namespace AppBundle\Entity\YB;

abstract class EnumRole {
    const MEMBER = 0;
    const ADMIN = 1;
    const SUPER_ADMIN = 2;
    const ADMIN_AND_SUPER_ADMIN = 3; // si Pierre ou Gonzague veulent créer un événement avec une organisation
}