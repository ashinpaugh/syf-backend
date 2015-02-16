<?php

namespace Moop\Bundle\HealthBundle\Response;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Handles common headers that need to be set to properly handle the preflight
 * requests.
 * 
 * @author Austin Shinpaugh
 */
class PreflightResponse extends CorsResponse
{
    /**
     * {@inheritdoc}
     */
    public function __construct($content = '', $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, []);
        
        $this->headers->add(array_merge(
            $this->getPreflightHeaders()->allPreserveCase(),
            $headers
        ));
    }
    
    /**
     * @return ResponseHeaderBag
     */
    protected function getPreflightHeaders()
    {
        return new ResponseHeaderBag([
            'Access-Control-Max-Age'           => $this->isCacheable() ? $this->getMaxAge() : 0,
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Headers'     => 'Content-Type, X-AUTH-TOKEN',
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            //'Access-Control-Allow-Credentials' => 'true',
        ]);
    }
}