<?php

namespace NS\SecurityBundle\Role;

use \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use \Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use \Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of ACLConverter
 *
 * @author gnat
 */
class ACLConverter
{
    private $rHierarchy;

    public function __construct(RoleHierarchyInterface $rInterface)
    {
        $this->rHierarchy = $rInterface;
    }

    /**
     *
     * @param UserInterface $user   - Array of ACL objects to check
     * @param type $irole           - Requested Role
     * 
     * @return array
     */
    public function getObjectIdsForRole(TokenInterface $token, $irole)
    {
        $object_ids = array();
        $reachable  = $this->getRoleHierarchy()->getReachableRoles($token->getRoles());

        foreach($token->getUser()->getAcls() as $acl)
        {
            // found an object id for this role
            if ($acl->getType()->equal($irole) || $this->findInMap($acl, $reachable, $irole))
            {
                $object_ids[] = $acl->getObjectId();
            }
        }

        return $object_ids;
    }

    protected function findInMap($acl, $reachable, $role)
    {
        foreach($reachable as $r)
        {
            if($acl->getType()->equal($r->getRole()))
                return true;
        }

        return false;
    }

    public function getRoleHierarchy()
    {
        return $this->rHierarchy;
    }
}
