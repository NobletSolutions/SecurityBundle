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
    
    public function setSecurityContext($security)
    {
        $this->_security = $security;
    }

    public function setSecuredQuery($qb)
    {
        $this->_queryBuilder = $qb;
    }
    
    public function getSecuredQuery()
    {
        return $this->_queryBuilder;
    }
    
    public function getSecurityContext()
    {
        return $this->_security;
    }
    
    public function secure(QueryBuilder $qb)
    {
        return $this->_queryBuilder->secure($qb);
    }
}
