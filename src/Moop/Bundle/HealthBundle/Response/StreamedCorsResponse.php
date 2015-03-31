<?php

namespace Moop\Bundle\HealthBundle\Response;


use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedCorsResponse extends StreamedResponse
{
    use CorsResponseTrait;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($callback = null, $status = 200, $headers = array())
    {
        parent::__construct($callback, $status, $headers);
        
        $this->initCors();
    }
}