<?php

namespace NS\SecurityBundle\Model;

/**
 *
 * @author gnat
 */
interface SecuredRepositoryInterface
{
    public function setSecurityContext($security);
    public function setSecuredQuery($qb);
}
