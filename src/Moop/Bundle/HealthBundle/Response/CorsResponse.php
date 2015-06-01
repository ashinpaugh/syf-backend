<?php

namespace Moop\Bundle\HealthBundle\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * The basic CORS response class that sets common required headers.
 * 
 * @author Austin Shinpaugh
 */
class CorsResponse extends Response
{
    use CorsResponseTrait;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
        
        $this->initCors();
    }
}