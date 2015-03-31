<?php

namespace Moop\Bundle\HealthBundle\Response;


use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Methods that CORS responses should call.
 * 
 * @author Austin Shinpaugh
 */
trait CorsResponseTrait
{
    /**
     * @var ResponseHeaderBag
     */
    protected $cors_headers;
    
    /**
     * Initialize the CORS headers.
     */
    public function initCors()
    {
        $this->cors_headers = new ResponseHeaderBag();
    
        if (isset($this->headers) && !$this->headers->has('Access-Control-Allow-Origin')) {
            $this->headers->set('Access-Control-Allow-Origin', '*');
        }
    }
    
    /**
     * Set the CORS headers for this request.
     */
    protected function appendCustomCorsHeaders()
    {
        $values = $this->cors_headers->allPreserveCase();
        $titles = implode(', ', array_keys($values));
    
        if (isset($this->headers)) {
            $this->headers->add(array_merge($values, [
                'Access-Control-Expose-Headers' => $titles,
            ]));
        }
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
        $this->appendCustomCorsHeaders();
        
        return parent::sendHeaders();
    }
}