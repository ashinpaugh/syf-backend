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
    protected $cors_headers;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->cors_headers = new ResponseHeaderBag();
        
        parent::__construct($content, $status, array_merge([
            'Access-Control-Allow-Origin' => '*',
        ], $headers));
    }
    
    /**
     * Set the CORS headers for this request.
     */
    protected function setCorsHeaders()
    {
        $values = $this->cors_headers->allPreserveCase();
        $titles = implode(', ', array_keys($values));
        
        $this->headers->add(array_merge($values, [
            'Access-Control-Expose-Headers' => $titles,
        ]));
    }
    
    /**
     * Add a custom CORS header. This will be exposed to the client.
     * 
     * @param String $key
     * @param Mixed  $value
     *
     * @return $this
     */
    public function setCustomHeader($key, $value)
    {
        $this->cors_headers->set($key, $value);
        return $this;
    }
    
    /**
     * Add a custom CORS header. These will be exposed to the client.
     * 
     * @param array $values
     *
     * @return $this
     */
    public function addCustomHeaders(array $values)
    {
        $this->cors_headers->add($values);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function sendHeaders()
    {
        $this->setCorsHeaders();
        
        return parent::sendHeaders();
    }
    
    /**
     * Factory method for chainability. Recreated so IDE's can see what
     * response is created.
     * 
     * @param string $content
     * @param int    $status
     * @param array  $headers
     *
     * @return $this
     */
    public static function create($content = '', $status = 200, $headers = array())
    {
        return parent::create($content, $status, $headers);
    }
}