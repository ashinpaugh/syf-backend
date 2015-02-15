<?php

namespace Moop\Bundle\HealthBundle\DependencyInjection\Security\Factory;

use Doctrine\Common\Util\Debug;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ApiFactory implements SecurityFactoryInterface
{
    const PROVIDER = 'moop.fat_secret.security.provider.api';
    const LISTENER = 'moop.fat_secret.security.firewall.api';
    
    protected $provider_id;
    protected $listener_id;
    
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
     * @return mixed
     */
    public function getProviderId()
    {
        return $this->provider_id;
    }
    
    /**
     * @param mixed $provider_id
     *
     * @return ApiFactory
     */
    public function setProviderId($provider_id)
    {
        $this->provider_id = $provider_id;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getListenerId()
    {
        return $this->listener_id;
    }
    
    /**
     * @param mixed $listener_id
     *
     * @return ApiFactory
     */
    public function setListenerId($listener_id)
    {
        $this->listener_id = $listener_id;
        return $this;
    }
    
    

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'api';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}