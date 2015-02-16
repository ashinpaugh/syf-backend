<?php

namespace Moop\Bundle\HealthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class ApiFactory implements SecurityFactoryInterface
{
    const PROVIDER = 'moop.fat_secret.security.provider.api';
    const LISTENER = 'moop.fat_secret.security.firewall.api';
    
    /**
     * {@inheritDoc}
     */
    public function create(
        ContainerBuilder $container,
        $id,
        $config,
        $user_provider,
        $default_entry_point
    ) {
        $provider_id = static::PROVIDER . ".{$id}";
        $listener_id = static::LISTENER . ".{$id}";

        $container
            ->setDefinition($provider_id, new DefinitionDecorator(static::PROVIDER))
        ;

        $container
            ->setDefinition($listener_id, new DefinitionDecorator(static::LISTENER))
        ;
        
        return array($provider_id, $listener_id, $default_entry_point);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'api';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}