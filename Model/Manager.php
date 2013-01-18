<?php

namespace NS\SecurityBundle\Model;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use \Symfony\Component\DependencyInjection\Container;
use \NS\SecurityBundle\Doctrine\SecuredQuery;
use \NS\SecurityBundle\Model\SecuredRepositoryInterface;

class Manager 
{
    private $_em;
    private $_securityContext;
    private $_securedQuery;
    
    public function __construct(SecurityContext $securityContext, EntityManager $em, SecuredQuery $sqb)
    {
        $this->_em              = $em;
        $this->_securityContext = $securityContext;
        $this->_securedQuery    = $sqb;
        
        return $this;
    }
    
    public function getEntityManager()
    {
        return $this->_em;
    }
    
    public function getSecurityContext()
    {
        return $this->_securityContext;
    }
    
    public function getRepository($class)
    {
        $repo = $this->_em->getRepository($class);
        if ($repo && $repo instanceof SecuredRepositoryInterface)
        {
            $repo->setSecurityContext($this->_securityContext);
            $repo->setSecuredQuery($this->_securedQuery);
        }
        
        return $repo;
    }
}
