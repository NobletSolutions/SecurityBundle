<?php

namespace NS\SecurityBundle\Annotation;


/**
 * Description of SecuredPath
 * @Annotation
 * @author gnat
 */
class SecuredPath
{
    private $paths;
    private $through;
    private $field;
    private $enabled = true;

    public function __construct($options)
    {
        if (isset($options['paths'])) {
            $this->paths = (is_array($options['paths'])) ? $options['paths'] : array($options['paths']);
        } else {
            throw new \Exception("Missing required property 'paths'");
        }

        if (isset($options['enabled'])) {
            $this->enabled = (bool)$options['enabled'];
        }

        if (isset($options['field'])) {
            $this->field = $options['field'];
        } elseif ($this->enabled === true) {
            throw new \Exception("Missing required property 'field'");
        }

        $this->through = (isset($options['through'])) ? $options['through']:null;
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

    public function getPaths()
    {
        return $this->paths;
    }

    public function appliesToPath($path)
    {
        return in_array($path, $this->paths);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }
}

