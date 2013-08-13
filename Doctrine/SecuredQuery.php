<?php

namespace NS\SecurityBundle\Doctrine;

use Symfony\Component\Security\Core\SecurityContext;
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
    private static $_alias_count = 30;
    
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
        if(!$this->securityContext->getToken())
            return;

        $this->user = $this->securityContext->getToken()->getUser();
        if(!($this->user instanceof SecuredEntityInterface))
            throw new \Exception("The user doesn't implement SecuredEntityInterface");
    }
    
    public function secure(QueryBuilder $query)
    {
        if(!$this->user)
            return $query;

        $this->queryBuilder = $query;
        $from               = $this->queryBuilder->getDQLPart('from');
        $alias              = $from[0]->getAlias();
        $class              = $from[0]->getFrom();
        
        $r                  = new AnnotationReader(); 
        $securedObject      = $r->getClassAnnotation(new \ReflectionClass($class),'NS\SecurityBundle\Annotation\Secured');

        // this object isn't secured
        if(!$securedObject)
             return $query;

        $aliases   = array();
        $aliases[] = $alias;
        $role      = false;
        $cond      = null;

        foreach($securedObject->getConditions() as $condition)
        {
            foreach($condition->getRoles() as $val)
            {
                if($this->securityContext->isGranted($val))
                {
                    $role = $val;
                    $cond = $condition;
                    break;
                }
            }

            if($role != false)
                break;
        }

        if($role !== false) // have a role
        {
            if(!$cond->isEnabled())
                return $query;

            $ids = $this->user->getACLObjectIdsForRole($role);

            if(count($ids) == 0)
                throw new \Exception('This user has no configured acls for role '.$role);

            if($cond->hasThrough())
            {
                foreach($cond->getThrough() as $association)
                {
                    $joins = $this->queryBuilder->getDQLPart('join');
                    $found = false;

                    if(isset($joins[$alias]))
                    {
                        foreach($joins[$alias] as $join)
                        {
                            if($join->getJoin() == "$alias.$association")
                            {
                                $found = true;
                                $aliases[] = $join->getAlias();
                            }
                        }
                    }

                    if(!$found)
                    {
                        $newalias = strtolower(substr($association,0,3)).self::$_alias_count++;
                        $this->queryBuilder->leftJoin(end($aliases).'.'.$association, $newalias);
                        $aliases[] = $newalias;
                    }
                }
            }

            if(count($ids) > 1)
                $this->queryBuilder->andWhere($this->queryBuilder->expr()->in(end($aliases).'.'.$condition->getField(),$ids));
            else if(count($ids) == 1)
            {
                $key = end($aliases).$condition->getField().rand(0,50);
                $this->queryBuilder
                     ->andWhere('('.end($aliases).'.'.$condition->getField()." = :$key )")
                     ->setParameter($key,current($ids));
            }
        }
        else
            throw new \Exception("This user has no roles");

        return $this->queryBuilder;
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
