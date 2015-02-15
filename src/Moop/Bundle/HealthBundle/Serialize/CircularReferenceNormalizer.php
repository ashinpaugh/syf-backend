<?php

namespace Moop\Bundle\HealthBundle\Serialize;


use Doctrine\Common\Util\Debug;
use Doctrine\Entity;
use ReflectionMethod;
use Symfony\Component\Serializer\Serializer;

class CircularReferenceNormalizer
{
    /**
     * @var Serializer
     */
    protected $serializer;
    
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
    
    public function ref($obj)
    {
        echo get_class($obj);
        //return $this->serializer->encode($obj, 'json');
    }
}