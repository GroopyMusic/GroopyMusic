<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;

/**
 * UserRolesManager defines a REVERSED role hierarchy while also handling normal role hierarchy.
 */
class UserRolesManager extends RoleHierarchy
{
    // This is the "normal" role hierarchy
    private $role_hierarchy;

    /**
     * Constructor.
     *
     * @param array $hierarchy An array defining the hierarchy
     */
    public function __construct(array $hierarchy)
    {
        $initial_hierarchy = $hierarchy;
        $this->role_hierarchy = new RoleHierarchy($initial_hierarchy);

        // Reverse the role hierarchy.
        $reversed = [];
        foreach ($hierarchy as $main => $roles) {
            foreach ($roles as $role) {
                $reversed[$role][] = $main;
            }
        }

        // Use the original algorithm to build the role map.
        parent::__construct($reversed);
    }

    /**
     * Helper function to get an array of strings
     *
     * @param array $roleNames An array of string role names
     *
     * @return array An array of string role names
     */
    public function getParentRoles(array $roleNames)
    {
        $roles = [];
        foreach ($roleNames as $roleName) {
            $roles[] = new Role($roleName);
        }

        $results = [];
        foreach ($this->getReachableRoles($roles) as $parent) {
            $results[] = $parent->getRole();
        }

        return $results;
    }

    // Returns all roles reachable with $user roles
    public function getAllRoles(User $user) {
       $reachableRoles = $user->getRoles();
       $normalRoleHierarchy = $this->role_hierarchy;

        foreach ($user->getRoles() as $role) {
            if (!isset($normalRoleHierarchy->map[$role])) {
                continue;
            }

            foreach ($normalRoleHierarchy->map[$role] as $r) {
                $reachableRoles[] = $r;
            }
        }

        return $reachableRoles;
    }

    public function userHasRole(User $user, $role) {
        return in_array($role, $this->getAllRoles($user));
    }
}