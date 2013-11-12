<?php

namespace NS\SecurityBundle\Model;

use Symfony\Component\Security\Core\SecurityContext;
use \NS\SecurityBundle\Doctrine\SecuredQuery;
use \NS\SecurityBundle\Model\SecuredRepositoryInterface;
use \Doctrine\Common\Persistence\ObjectManager;

class Manager implements ObjectManager
{
    private $_em;
    private $_securityContext;
    private $_securedQuery;
    
    public function __construct(SecurityContext $securityContext, ObjectManager $em, SecuredQuery $sqb)
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
            $repo->setManager($this);
        }
        
        return $repo;
    }
    
    public function persist($object)
    {
        return $this->_em->persist($object);
    }
    
    public function flush()
    {
        return $this->_em->flush();
    }

    public function remove($object)
    {
        return $this->_em->remove($object);
    }

    public function clear($objectName = null) {
        return $this->_em->clear($objectName);
    }

    public function contains($object) {
        return $this->_em->contains($object);
    }

    public function detach($object) {
        return $this->_em->detach($object);
    }

    public function find($className, $id) {
        return $this->_em->find($className, $id);
    }

    public function getClassMetadata($className) {
        return $this->_em->getClassMetadata($className);
    }

    public function getMetadataFactory() {
        return $this->_em->getMetadataFactory();
    }

    public function initializeObject($obj) {
        return $this->_em->initializeObject($obj);
    }

    public function merge($object) {
        return $this->_em->merge($object);
    }

    public function refresh($object) {
        return $this->_em->refresh($object);
    }
}
