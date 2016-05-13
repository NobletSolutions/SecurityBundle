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
     * @var string|null
     */
    private $field    = null;

    /**
     * @var bool
     */
    private $enabled  = true;

    /**
     * @var string|null
     */
    private $class    = null;

    /**
     * @var string|null
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

        if ($this->enabled === true ) {
            if(!isset($options['field']) || (!isset($options['relation']) && !isset($options['class']))) {
                throw new MissingOptionsException("Missing required property 'field' or 'relation' and 'class'", $options);
            }
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
     * @return string|null
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
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getRelation()
    {
        return $this->relation;
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
