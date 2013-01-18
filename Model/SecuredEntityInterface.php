<?php

namespace NS\SecurityBundle\Model;

/**
 * Description of SecuredEntityInterface
 *
 * @author gnat
 */
interface SecuredEntityInterface
{
    public function getACLObjectIdsForRole($role);
}
