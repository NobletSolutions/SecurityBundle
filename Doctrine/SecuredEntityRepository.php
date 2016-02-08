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
    private $queryBuilder;
    private $manager;
    
    public function setSecuredQuery(SecuredQuery $qb)
    {
        $this->queryBuilder = $qb;

        return $this;
    }

    public function getSecuredQuery()
    {
        return $this->queryBuilder;
    }

    public function hasSecuredQuery()
    {
        return $this->queryBuilder !== null;
    }

    public function secure(QueryBuilder $qb)
    {
        if ($this->queryBuilder) {
            return $this->queryBuilder->secure($qb);
        }

        throw new \RuntimeException("Calling secure on a non-object");
    }

    public function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    public function getManager()
    {
        return $this->manager;
    }
}
