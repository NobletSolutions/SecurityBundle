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
    private $apply_to;
    
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
        
//        if(isset($options['apply_to']))
//            $this->apply_to = $options['apply_to'];
//        else
//            throw new \Exception("Missing required property 'apply_to'");
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
    
    public function getApplyTo()
    {
        return $this->apply_to;
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
