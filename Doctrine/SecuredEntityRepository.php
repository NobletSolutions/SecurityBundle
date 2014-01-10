<?php

namespace NS\SecurityBundle\Doctrine;

use Doctrine\ORM\EntityRepository;
use NS\SecurityBundle\Model\SecuredRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of SecuredEntityRepository
 *
 * @author gnat
 */
class SecuredEntityRepository extends EntityRepository implements SecuredRepositoryInterface
{
    private $_security;
    private $_queryBuilder;
    private $_manager;
    
    public function setSecurityContext($security)
    {
        $this->_security = $security;

        return $this;
    }

    public function getSecurityContext()
    {
        return $this->_security;
    }

    public function setSecuredQuery($qb)
    {
        $this->_queryBuilder = $qb;

        return $this;
    }

    public function getSecuredQuery()
    {
        return $this->_queryBuilder;
    }

    public function hasSecuredQuery()
    {
        return $this->_queryBuilder != null;
    }

    public function secure(QueryBuilder $qb)
    {
        return $this->_queryBuilder->secure($qb);
    }

    public function setManager($manager)
    {
        $this->_manager = $manager;

        return $this;
    }

    public function getManager()
    {
        return $this->_manager;
    }
}
