<?php

namespace NS\SecurityBundle\Model;

use Symfony\Component\Security\Core\SecurityContextInterface;
use \NS\SecurityBundle\Doctrine\SecuredQuery;
use \NS\SecurityBundle\Model\SecuredRepositoryInterface;
use \Doctrine\Common\Persistence\ObjectManager;

class Manager implements ObjectManager
{
    private $entityManager;

    private $securityContext;

    private $securedQuery;

    /**
     *
     * @param SecurityContextInterface $securityContext
     * @param ObjectManager $entityMgr
     * @param SecuredQuery $securedQuery
     * @return \NS\SecurityBundle\Model\Manager
     */
    public function __construct(SecurityContextInterface $securityContext, ObjectManager $entityMgr, SecuredQuery $securedQuery)
    {
        $this->entityManager   = $entityMgr;
        $this->securityContext = $securityContext;
        $this->securedQuery    = $securedQuery;

        return $this;
    }

    /**
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     *
     * @return SecurityContextInterface
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($class)
    {
        $repo = $this->entityManager->getRepository($class);
        if ($repo && $repo instanceof SecuredRepositoryInterface)
        {
            $repo->setSecurityContext($this->securityContext);
            $repo->setSecuredQuery($this->securedQuery);
            $repo->setManager($this);
        }

        return $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object)
    {
        return $this->entityManager->persist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        return $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        return $this->entityManager->remove($object);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($objectName = null)
    {
        return $this->entityManager->clear($objectName);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($object)
    {
        return $this->entityManager->contains($object);
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        return $this->entityManager->detach($object);
    }

    /**
     * {@inheritdoc}
     */
    public function find($className, $id)
    {
        return $this->entityManager->find($className, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata($className)
    {
        return $this->entityManager->getClassMetadata($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory()
    {
        return $this->entityManager->getMetadataFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeObject($obj)
    {
        return $this->entityManager->initializeObject($obj);
    }

    /**
     * {@inheritdoc}
     */
    public function merge($object)
    {
        return $this->entityManager->merge($object);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh($object)
    {
        return $this->entityManager->refresh($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork()
    {
        return $this->entityManager->getUnitOfWork();
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->entityManager, $name))
            return call_user_func_array(array($this->entityManager, $name), $arguments);
    }
}