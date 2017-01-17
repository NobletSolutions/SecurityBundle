<?php

namespace NS\SecurityBundle\Annotation;

use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Description of Secured
 * @Annotation
 * @author gnat
 * @Target({"CLASS"})
 */
class Secured
{
    /**
     * @var array
     */
    private $conditions;

    /**
     * Secured constructor.
     * @param $options
     */
    public function __construct($options)
    {
        if (isset($options['conditions'])) {
            $this->conditions = $options['conditions'];
        } else {
            throw new MissingOptionsException("Missing required property 'conditions'",$options);
        }
    }

    /**
     * @return mixed
     */
    public function getConditions()
    {
        return $this->conditions;
    }
}
