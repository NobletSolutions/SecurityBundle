<?php

namespace NS\SecurityBundle\Annotation;

use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Description of SecuredCondition
 * @Annotation
 * @author gnat
 */
class SecuredCondition
{
    /**
     * @var array
     */
    private $roles;

    /**
     * @var array
     */
    private $through;

    /**
     * @var null
     */
    private $field    = null;

    /**
     * @var bool
     */
    private $enabled  = true;

    /**
     * @var null
     */
    private $class    = null;

    /**
     * @var null
     */
    private $relation = null;

    /**
     * SecuredCondition constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (isset($options['through'])) {
            if (!is_array($options['through'])) {
                $options['through'] = array($options['through']);
            }

            $this->through = $options['through'];
        }

        if (isset($options['roles'])) {
            $this->roles = (is_array($options['roles'])) ? $options['roles'] : array($options['roles']);
        } else {
            throw new MissingOptionsException("Missing required property 'roles'",$options);
        }

        if (isset($options['enabled'])) {
            $this->enabled = (bool)$options['enabled'];
        }

        if ($this->enabled === true && !isset($options['field']) && (!isset($options['relation']) || !isset($options['class']))) {
            throw new MissingOptionsException("Missing required property 'field' or 'relation' and 'class'",$options);
        }

        if (isset($options['field'])) {
            $this->field = $options['field'];
        } else {
            $this->class = $options['class'];
            $this->relation = $options['relation'];
        }
    }

    /**
     * @return bool
     */
    public function hasThrough()
    {
        return !empty($this->through);
    }

    /**
     * @return array
     */
    public function getThrough()
    {
        return $this->through;
    }

    /**
     * @return null
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param $role
     * @return bool
     */
    public function appliesToRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $enabled
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return null
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param $relation
     * @return $this
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasField()
    {
        return !is_null($this->field);
    }

    /**
     * @return bool
     */
    public function hasRelation()
    {
        return !is_null($this->relation);
    }
}
