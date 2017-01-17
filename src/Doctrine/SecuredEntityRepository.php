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
    /**
     * @var SecuredQuery
     */
    private $queryBuilder;

    /**
     * @param SecuredQuery $qb
     * @return $this
     */
    public function setSecuredQuery(SecuredQuery $qb)
    {
        $this->queryBuilder = $qb;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecuredQuery()
    {
        return $this->queryBuilder;
    }

    /**
     * @return bool
     */
    public function hasSecuredQuery()
    {
        return $this->queryBuilder !== null;
    }

    /**
     * @param QueryBuilder $qb
     * @return mixed
     */
    public function secure(QueryBuilder $qb)
    {
        if ($this->queryBuilder) {
            return $this->queryBuilder->secure($qb);
        }

        throw new \RuntimeException("Calling secure on a non-object");
    }

    /**
     * @param $alias
     * @return mixed
     */
    public function createSecuredQueryBuilder($alias)
    {
        return $this->secure($this->createQueryBuilder($alias));
    }
}
