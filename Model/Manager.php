<?php

namespace NS\SecurityBundle\Model;

use \Doctrine\ORM\Decorator\EntityManagerDecorator;
use NS\SecurityBundle\Doctrine\SecuredQuery;

class Manager extends EntityManagerDecorator
{
    private $securedQuery;

    /**
     * @param mixed $securedQuery
     * @return Manager
     */
    public function setSecuredQuery(SecuredQuery $securedQuery)
    {
        $this->securedQuery = $securedQuery;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($class)
    {
        $repo = $this->wrapped->getRepository($class);

        if ($repo && $repo instanceof SecuredRepositoryInterface) {
            $repo->setSecuredQuery($this->securedQuery);
        }

        return $repo;
    }
}

