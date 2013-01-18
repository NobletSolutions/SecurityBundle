<?php

namespace NS\SecurityBundle\Annotation;

use Doctrine\Common\Annotations\Reader;

/**
 * Description of Secured
 * @Annotation
 * @author gnat
 */
class Secured
{
    private $conditions;
    
    public function __construct($options)
    {
        if(isset($options['conditions']))
            $this->conditions = $options['conditions'];
        else
            throw new \Exception("Missing required property 'conditions'");
    }
 
    public function getConditions()
    {
        return $this->conditions;
    }
}
