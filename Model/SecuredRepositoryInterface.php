<?php

namespace NS\SecurityBundle\Model;

/**
 *
 * @author gnat
 */
interface SecuredRepositoryInterface
{
    public function setSecuredQuery($qb);
}
