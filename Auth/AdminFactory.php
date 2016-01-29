<?php

namespace NS\SecurityBundle\Auth;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
 
class AdminFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();

        $this->addOption('user_parameter', '_user');
    }

    public function getKey()
    {
        return 'admin-login';
    }
 
    protected function getListenerId()
    {
        return 'ns.security.authentication.listener';
    }
 
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication_provider.ns_security_'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('security.authentication_provider.ns_security'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(2, $id);
        
        return $provider;
    }
}

