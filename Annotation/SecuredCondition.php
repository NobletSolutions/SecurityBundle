<?php

namespace NS\SecurityBundle\Annotation;

use Doctrine\Common\Annotations\Reader;

/**
 * Description of Secured
 * @Annotation
 * @author gnat
 */
class SecuredCondition
{
    private $roles;
    private $through;
    private $field;
    
    public function __construct($options)
    {
        $this->through = (isset($options['through'])) ? $options['through']:null;

        if(isset($options['roles']))
            $this->roles = (is_array($options['roles']))?$options['roles']:array($options['roles']);
        else
            throw new \Exception("Missing required property 'roles'");
        
        if(isset($options['field']))
            $this->field = $options['field'];
        else
            throw new \Exception("Missing required property 'field'");
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
}
