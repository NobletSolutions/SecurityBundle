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
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy, $roleClass)
    {
        $this->roleHierarchy = $roleHierarchy;

        if(!class_exists($roleClass))
            throw new \RuntimeException("$roleClass does not exist");

        $this->roleClass     = $roleClass;
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

        try
        {
            $role = new $this->roleClass($irole);
        }
        catch(\UnexpectedValueException $e)
        {
            return null;
        }

        $acls  = $token->getUser()->getAcls();

        if(empty($acls))
            return null;

        $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
        die("<pre>".print_r($roles,true)."</pre> ".$irole." ".$role."<pre>".print_r($acls,true)."</pre>");
        foreach($acls as $acl)
        {
            if($acl->getType()->equal($role)) // found an object id for this role
                $object_ids[] = $acl->getObjectId();
        }

        return $object_ids;
    }
}
