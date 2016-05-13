<?php

namespace NS\SecurityBundle\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\QueryBuilder;
use NS\SecurityBundle\Annotation\Secured;
use NS\SecurityBundle\Annotation\SecuredCondition;
use NS\SecurityBundle\Role\ACLConverter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Description of SecuredQuery
 *
 * @author gnat
 */
class SecuredQuery
{
    private $tokenStorage;
    private $authChecker;
    private $queryBuilder;
    private $aclRetriever;

    private static $aliasCount = 30;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authChecker, ACLConverter $aclRetriever)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker  = $authChecker;
        $this->aclRetriever = $aclRetriever;
    }

    /**
     *
     * @param QueryBuilder $query
     * @return QueryBuilder
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function secure(QueryBuilder $query)
    {
        if (!$this->tokenStorage->getToken()) {
            return $query;
        }

        $this->queryBuilder = $query;
        $from               = $this->queryBuilder->getDQLPart('from');
        $class              = $from[0]->getFrom();
        $reader             = new AnnotationReader();
        $securedObject      = $reader->getClassAnnotation(new \ReflectionClass($class), 'NS\SecurityBundle\Annotation\Secured');

        // this object isn't secured
        if (!$securedObject) {
            return $query;
        }

        $alias = $from[0]->getAlias();
        $aliases = array($alias);

        /** @var SecuredCondition $condition */
        list($role, $condition) = $this->getRole($securedObject);

        // have a role
        if ($role !== false) {
            if (!$condition->isEnabled()) {
                return $query;
            }

            $ids = $this->aclRetriever->getObjectIdsForRole($this->tokenStorage->getToken(), $role);

            if (count($ids) == 0) {
                throw new \RuntimeException('This user has no configured acls for role ' . $role);
            }

            if ($condition->hasThrough()) {
                $this->handleThrough($condition, $alias, $aliases);
            }

            if ($condition->hasField()) {
                $this->handleField($condition, $ids, $aliases);
            } elseif ($condition->hasRelation()) {
                $this->handleRelation($condition, $ids, $aliases);
            }
        } else {
            throw new \RuntimeException("This user has no roles");
        }

        return $this->queryBuilder;
    }

    /**
     * @param Secured $securedObject
     * @return array
     */
    protected function getRole(Secured $securedObject)
    {
        foreach ($securedObject->getConditions() as $condition) {
            foreach ($condition->getRoles() as $val) {
                if ($this->authChecker->isGranted($val)) {
                    return array($val,$condition);
                }
            }
        }

        return array(false,null);
    }

    /**
     * @param SecuredCondition $cond
     * @param array $ids
     * @param array $aliases
     */
    protected function handleRelation(SecuredCondition $cond, array $ids, array &$aliases)
    {
        $em = $this->queryBuilder->getEntityManager();
        if (count($ids) == 1) {
            $ref = $em->getReference($cond->getClass(), current($ids));
            $key = $this->getKey($cond, $aliases);
            $this->queryBuilder
                ->andWhere(sprintf('(%s.%s = %s)', end($aliases), $cond->getRelation(), $key))
                ->setParameter($key, $ref);
        } else {
            $where = array();
            foreach ($ids as &$id) {
                $ref = $em->getReference($cond->getClass(), $id);
                $key = $this->getKey($cond, $aliases);
                $where[] = sprintf('(%s.%s = %s)', end($aliases), $cond->getRelation(), $key);
                $this->queryBuilder->setParameter($key, $ref);
            }

            if (count($where) != count($ids)) {
                throw new \RuntimeException("We don't have as many where's as ids!");
            }

            $this->queryBuilder->andWhere('(' . implode(" OR ", $where) . ')');
        }
    }

    /**
     * @param SecuredCondition $cond
     * @param array $ids
     * @param array $aliases
     */
    protected function handleField(SecuredCondition $cond, array $ids, array &$aliases)
    {
        if (count($ids) > 1) {
            $this->queryBuilder->andWhere($this->queryBuilder->expr()->in(end($aliases) . '.' . $cond->getField(), $ids));
        } elseif (count($ids) == 1) {
            $key = $this->getKey($cond, $aliases);
            $this->queryBuilder
                ->andWhere(sprintf('(%s.%s = %s)', end($aliases), $cond->getField(), $key))
                ->setParameter($key, current($ids));
        }
    }

    /**
     * @param SecuredCondition $condition
     * @param array $aliases
     * @return string
     */
    protected function getKey(SecuredCondition $condition, array $aliases)
    {
        static $_key = 49;

        return sprintf(":%s%s%s", end($aliases), $condition->getField(), $_key++);
    }

    /**
     * @param SecuredCondition $cond
     * @param $alias
     * @param array $aliases
     */
    protected function handleThrough(SecuredCondition $cond, $alias, array &$aliases)
    {
        $joins = $this->queryBuilder->getDQLPart('join');

        foreach ($cond->getThrough() as $association) {
            $found = false;

            if (isset($joins[$alias])) {
                $found = $this->findJoin($joins, $alias, $association, $aliases);
            }

            if (!$found) {
                $newalias = strtolower(substr($association, 0, 3)).self::$aliasCount++;
                $this->queryBuilder->leftJoin(end($aliases).'.'.$association, $newalias);
                $aliases[] = $newalias;
            }
        }
    }

    /**
     * @param array $joins
     * @param $alias
     * @param $association
     * @param array $aliases
     * @return bool
     */
    protected function findJoin(array $joins, $alias, $association, array &$aliases)
    {
        foreach ($joins[$alias] as $join) {
            if ($join->getJoin() == "$alias.$association") {
                $aliases[] = $join->getAlias();
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
