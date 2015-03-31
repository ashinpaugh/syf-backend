<?php

namespace Moop\Bundle\HealthBundle;

use Moop\Bundle\HealthBundle\DependencyInjection\Security\Factory\ApiFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MoopHealthBundle extends Bundle
{
    public function boot()
    {
        parent::boot();
    }
    
    
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        /* @var SecurityExtension $security */
        $security = $container->getExtension('security');
        $security->addSecurityListenerFactory(new ApiFactory());
    }
}
