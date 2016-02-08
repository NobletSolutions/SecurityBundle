<?php

namespace NS\SecurityBundle\Role;

use NS\SecurityBundle\Entity\BaseACL;
use \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use \Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Description of ACLConverter
 *
 * @author gnat
 */
class ACLConverter
{
    /**
     * @var RoleHierarchyInterface
     */
    private $rHierarchy;

    /**
     *
     * @param RoleHierarchyInterface $rInterface
     */
    public function __construct(RoleHierarchyInterface $rInterface)
    {
        $this->rHierarchy = $rInterface;
    }

    /**
     *
     * @param TokenInterface $token - Array of ACL objects to check
     * @param string $inputRole     - Requested Role
     * 
     * @return array
     */
    public function getObjectIdsForRole(TokenInterface $token, $inputRole)
    {
        $object_ids = array();
        $reachable  = $this->getRoleHierarchy()->getReachableRoles($token->getRoles());

        foreach($token->getUser()->getAcls() as $acl) {
            // found an object id for this role
            if ($acl->getType()->equal($inputRole) || $this->findInMap($acl, $reachable)) {
                $object_ids[] = $acl->getObjectId();
            }
        }

        return $object_ids;
    }

    /**
     *
     * @param BaseACL $acl
     * @param array $reachable
     * @return boolean
     */
    protected function findInMap(BaseACL $acl, array $reachable)
    {
        foreach ($reachable as $role) {
            if ($acl->getType()->equal($role->getRole())) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return RoleHierarchyInterface
     */
    public function getRoleHierarchy()
    {
        return $this->rHierarchy;
    }
}
