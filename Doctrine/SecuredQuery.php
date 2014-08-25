<?php

namespace NS\SecurityBundle\Doctrine;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use NS\SecurityBundle\Model\SecuredEntityInterface;

/**
 * Description of SecuredQuery
 *
 * @author gnat
 */
class SecuredQuery
{
    private $securityContext;
    private $securityConditions;
    private $queryBuilder;
    private $user;
    private $aclRetriever;

    private static $_alias_count = 30;

    public function __construct(SecurityContextInterface $securityContext, \NS\SecurityBundle\Role\ACLConverter $aclRetriever)
    {
        if(is_null($securityContext) || !$securityContext->getToken())
            return;

        $this->securityContext = $securityContext;

        if(!$this->securityContext->getToken()->getUser() instanceof SecuredEntityInterface)
            throw new \RuntimeException("The user doesn't implement SecuredEntityInterface");

        $this->aclRetriever = $aclRetriever;
    }

    public function secure(QueryBuilder $query)
    {
        if(!$this->securityContext || !$this->securityContext->getToken())
            return $query;

        $this->queryBuilder = $query;
        $from               = $this->queryBuilder->getDQLPart('from');
        $class              = $from[0]->getFrom();
        $r                  = new AnnotationReader(); 
        $securedObject      = $r->getClassAnnotation(new \ReflectionClass($class),'NS\SecurityBundle\Annotation\Secured');

        // this object isn't secured
        if(!$securedObject)
            return $query;

        $alias     = $from[0]->getAlias();
        $aliases   = array($alias);
        $role      = false;
        $cond      = null;

        $this->getRole($securedObject,$role,$cond);

        if($role !== false) // have a role
        {
            if(!$cond->isEnabled())
                return $query;

            $ids = $this->aclRetriever->getObjectIdsForRole($this->securityContext->getToken(), $role);//$this->user->getACLObjectIdsForRole($role);

            if(count($ids) == 0)
                throw new \RuntimeException('This user has no configured acls for role '.$role);

            if($cond->hasThrough())
                $this->handleThrough($cond, $alias, $aliases);

            if($cond->hasField())
                $this->handleField($cond,$ids,$aliases);
            else if($cond->hasRelation())
                $this->handleRelation($cond,$ids,$aliases);
            else
                throw new \InvalidArgumentException("The condition has neither fields nor classes - this should never be thrown");
        }
        else
            throw new \RuntimeException("This user has no roles");

        return $this->queryBuilder;
    }

    protected function getRole($securedObject, &$role, &$cond)
    {
        foreach($securedObject->getConditions() as $condition)
        {
            foreach($condition->getRoles() as $val)
            {
                if($this->securityContext->isGranted($val))
                {
                    $role = $val;
                    $cond = $condition;
                    break 2;
                }
            }
        }
    }

    protected function handleRelation($cond, array $ids, array &$aliases)
    {
        if(count($ids) == 1)
        {
            $ref = $this->queryBuilder->getEntityManager()->getReference($cond->getClass(),current($ids));
            $key = end($aliases).$cond->getRelation().rand(0,50);
            $this->queryBuilder
                 ->andWhere('('.end($aliases).'.'.$cond->getRelation()." = :$key )")
                 ->setParameter($key,$ref);
        }
        else
        {
            $where = array();
            foreach($ids as $id)
            {
                $ref     = $this->queryBuilder->getEntityManager()->getReference($cond->getClass(),$id);
                $key     = end($aliases).$cond->getRelation().rand(0,50);
                $where[] = '('.end($aliases).'.'.$cond->getRelation()." = :$key )";

                $this->queryBuilder->setParameter($key,$ref);
            }

            $this->queryBuilder->andWhere( '('.implode(" OR ", $where).')');
        }
    }

    protected function handleField($cond, array $ids, array &$aliases)
    {
        if(count($ids) > 1)
            $this->queryBuilder->andWhere($this->queryBuilder->expr()->in(end($aliases).'.'.$cond->getField(),$ids));
        else if(count($ids) == 1)
        {
            $key = end($aliases).$cond->getField().rand(0,50);
            $this->queryBuilder
                 ->andWhere('('.end($aliases).'.'.$cond->getField()." = :$key )")
                 ->setParameter($key,current($ids));
        }
    }

    protected function handleThrough($cond, $alias, array &$aliases)
    {
        foreach($cond->getThrough() as $association)
        {
            $joins = $this->queryBuilder->getDQLPart('join');

            if(isset($joins[$alias]) && !$this->findJoin($joins, $alias, $association, $aliases))
            {
                $newalias = strtolower(substr($association,0,3)).self::$_alias_count++;
                $this->queryBuilder->leftJoin(end($aliases).'.'.$association, $newalias);
                $aliases[] = $newalias;
            }
        }
    }

    protected function findJoin(array $joins, $alias, $association, array &$aliases)
    {
        foreach($joins[$alias] as $join)
        {
            if($join->getJoin() == "$alias.$association")
            {
                $aliases[] = $join->getAlias();
                return true;
            }
        }

        return false;
    }

    public function getSecurityConditions()
    {
        return $this->securityConditions;
    }
    
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
