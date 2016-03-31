<?php

namespace NS\SecurityBundle\Model;

use NS\SecurityBundle\Doctrine\SecuredQuery;

/**
 *
 * @author gnat
 */
interface SecuredRepositoryInterface
{
    public function setSecuredQuery(SecuredQuery $qb);
}
