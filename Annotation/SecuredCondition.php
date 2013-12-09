<?php

namespace NS\SecurityBundle\Annotation;

use Doctrine\Common\Annotations\Reader;

/**
 * Description of SecuredCondition
 * @Annotation
 * @author gnat
 */
class SecuredCondition
{
    private $roles;
    private $through;
    private $field    = null;
    private $enabled  = true;
    private $class    = null;
    private $relation = null;
    
    public function __construct($options)
    {
        $this->through = (isset($options['through'])) ? $options['through']:null;

        if(isset($options['roles']))
            $this->roles = (is_array($options['roles'])) ? $options['roles'] : array($options['roles']);
        else
            throw new \Exception("Missing required property 'roles'");

        if(isset($options['enabled']))
            $this->enabled = (bool)$options['enabled'];

        if($this->enabled === true && !isset($options['field']) && (!isset($options['relation']) || !isset($options['class'])))
            throw new \Exception("Missing required property 'field' or 'relation' and 'class'");

        if(isset($options['field']))
            $this->field = $options['field'];
        else
        {
            $this->class = $options['class'];
            $this->relation = $options['relation'];
        }
    }
 
    public function hasThrough()
    {
        return !empty($this->through);
    }
    
    public function getThrough()
    {
        return $this->through;
    }
    
    public function getField()
    {
        return $this->field;
    }
    
    public function getRoles()
    {
        return $this->roles;
    }
    
    public function appliesToRole($role)
    {
        return in_array($role, $this->roles);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function getRelation()
    {
        return $this->relation;
    }

    public function setRelation($relation)
    {
        $this->relation = $relation;
        return $this;
    }

    public function hasField()
    {
        return !is_null($this->field);
    }

    public function hasRelation()
    {
        return !is_null($this->relation);
    }
}
