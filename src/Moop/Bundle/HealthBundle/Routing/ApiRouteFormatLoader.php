<?php

namespace Moop\Bundle\HealthBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ApiRouteFormatLoader extends Loader
{
    protected $loaded = false;
    
    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new \RuntimeException('API Format Loader fired twice.');
        }
        
        $collection = new RouteCollection();
        //$resource   = '@MoopHealthBundle/Controller/';

        $routes = $this->import($resource, 'annotation');
        
        /* @var Route $route */
        foreach ($routes as $route) {
            $route->setPath(
                $route->getPath() . ".{_format}"
            );
            
            $route->setMethods(
                array_merge($route->getMethods(), ['OPTIONS'])
            );
        }

        $collection->addCollection($routes);
        $collection->addPrefix('/v%moop.health.api.version%');
        $collection->setHost("api.%domain%");
        
        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'api_format';
    }
}