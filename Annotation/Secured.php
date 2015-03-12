<?php

namespace NS\SecurityBundle\Annotation;

/**
 * Description of Secured
 * @Annotation
 * @author gnat
 * @Target({"CLASS"})
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
