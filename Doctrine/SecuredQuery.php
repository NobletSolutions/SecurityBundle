<?php

namespace NS\SecurityBundle\Doctrine;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\ORM\QueryBuilder;
use \NS\SecurityBundle\Model\SecuredEntityInterface;
use \NS\SecurityBundle\Role\ACLConverter;
use \Symfony\Component\Security\Core\SecurityContextInterface;
use \Symfony\Component\Security\Core\User\UserInterface;

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

    public function __construct(SecurityContextInterface $securityContext, ACLConverter $aclRetriever)
    {
        if(is_null($securityContext) || !$securityContext->getToken() || !$securityContext->getToken()->getUser() instanceof UserInterface)
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

        $alias   = $from[0]->getAlias();
        $aliases = array($alias);

        list($role,$condition) = $this->getRole($securedObject);

        if($role !== false) // have a role
        {
            if(!$condition->isEnabled())
                return $query;

            $ids = $this->aclRetriever->getObjectIdsForRole($this->securityContext->getToken(), $role);

            if(count($ids) == 0)
                throw new \RuntimeException('This user has no configured acls for role '.$role);

            if($condition->hasThrough())
                $this->handleThrough($condition, $alias, $aliases);

            if($condition->hasField())
                $this->handleField($condition, $ids, $aliases);
            else if($condition->hasRelation())
                $this->handleRelation($condition, $ids, $aliases);
            else
                throw new \InvalidArgumentException("The condition has neither fields nor classes - this should never be thrown");
        }
        else
            throw new \RuntimeException("This user has no roles");

        return $this->queryBuilder;
    }

    protected function getRole($securedObject)
    {
        foreach($securedObject->getConditions() as $condition)
        {
            foreach($condition->getRoles() as $val)
            {
                if($this->securityContext->isGranted($val))
                    return array($val,$condition);
            }
        }

        return array(false,null);
    }

    protected function handleRelation($cond, array $ids, array &$aliases)
    {
        $em = $this->queryBuilder->getEntityManager();
        if(count($ids) == 1)
        {
            $ref = $em->getReference($cond->getClass(),current($ids));
            $key = $this->getKey($cond, $aliases);
            $this->queryBuilder
                 ->andWhere(sprintf('(%s.%s = %s)',end($aliases),$cond->getRelation(),$key))
                 ->setParameter($key,$ref);
        }
        else
        {
            $where = array();
            foreach($ids as &$id)
            {
                $ref     = $em->getReference($cond->getClass(),$id);
                $key     = $this->getKey($cond, $aliases);
                $where[] = sprintf('(%s.%s = %s)',end($aliases),$cond->getRelation(),$key);

                $this->queryBuilder->setParameter($key,$ref);
            }

            if(count($where) != count($ids))
                throw new \RuntimeException("We don't have as many where's as ids!");

            $this->queryBuilder->andWhere( '('.implode(" OR ", $where).')');
        }
    }

    protected function handleField($cond, array $ids, array &$aliases)
    {
        if(count($ids) > 1)
            $this->queryBuilder->andWhere($this->queryBuilder->expr()->in(end($aliases).'.'.$cond->getField(),$ids));
        else if(count($ids) == 1)
        {
            $key = $this->getKey($cond, $aliases);
            $this->queryBuilder
                 ->andWhere(sprintf('(%s.%s = %s)',end($aliases),$cond->getField(),$key))
                 ->setParameter($key,current($ids));
        }
    }

    protected function getKey($condition,array $aliases)
    {
        return ":".end($aliases).$condition->getField().rand(0,50);
    }

    protected function handleThrough($cond, $alias, array &$aliases)
    {
        $joins = $this->queryBuilder->getDQLPart('join');

        foreach($cond->getThrough() as $association)
        {
            $found = false;

            if(isset($joins[$alias]))
                $found = $this->findJoin($joins, $alias, $association, $aliases);

            if(!$found)
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
