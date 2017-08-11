<?php

namespace Moop\Bundle\HealthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DynamicServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->setAlias(
            'moop.health.security.token_encoder',
            $container->getParameter('moop.health.security.encoder.provider_id')
        );
    }
}