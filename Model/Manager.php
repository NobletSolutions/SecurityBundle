<?php

namespace NS\SecurityBundle\Model;

use \Doctrine\ORM\Decorator\EntityManagerDecorator;
use \NS\SecurityBundle\Model\SecuredRepositoryInterface;
use \Symfony\Component\DependencyInjection\ContainerAwareInterface;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class Manager extends EntityManagerDecorator implements ContainerAwareInterface
{
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getRepository($class)
    {
        $repo = $this->wrapped->getRepository($class);
        if ($repo && $repo instanceof SecuredRepositoryInterface)
        {
            $repo->setSecurityContext($this->container->get('security.context'));
            $repo->setSecuredQuery($this->container->get('ns.security.query'));
        }

        return $repo;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

