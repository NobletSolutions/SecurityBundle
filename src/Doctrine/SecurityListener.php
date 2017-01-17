<?php

namespace NS\SecurityBundle\Doctrine;

class SecurityListener
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
                'prePersist',
                'onFlush',
                'loadClassMetadata'
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
