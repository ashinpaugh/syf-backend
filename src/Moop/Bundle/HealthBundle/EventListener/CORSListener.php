<?php

namespace Moop\Bundle\HealthBundle\EventListener;

use Doctrine\Common\Util\Debug;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Serializer;

class CORSListener
{
    /**
     * @var Serializer
     */
    protected $serializer;
    
    
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
    
    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        try {
            $response = $this->buildResponse($event);
            $response->headers->add([
                'Access-Control-Allow-Origin' => '*',
            ]);
            
            $event->setResponse($response);
        } catch (\Exception $e) {
            $event->getRequest()->attributes->set('_format', 'html');
            throw $e;
        }
    }
    
    /**
     * Intercepts the OPTIONS request and sets the necessary Headers.
     * 
     * @param GetResponseEvent $e
     */
    public function onRequest(GetResponseEvent $e)
    {
        if (!$e->isMasterRequest() || !$e->getRequest()->isMethod('OPTIONS')) {
            return;
        }
        
        $e->setResponse(
            new Response('', 200, [
                'Access-Control-Max-Age'        => '3600',
                'Access-Control-Allow-Origin'   => '*',
                'Access-Control-Allow-Headers'  => 'Content-Type',
                'Access-Control-Allow-Methods'  => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
                //'Access-Control-Expose-Headers' => '',
            ])
        );
    }
    
    /**
     * Format the response data into the appropriate format.
     * 
     * @param GetResponseForControllerResultEvent $event
     *
     * @return Response
     */
    private function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $format  = $event->getRequest()->get('_format');
        $result  = $event->getControllerResult();
        $content = json_encode($result);
        
        //$content = $this->serializer->serialize($result, $format);
        
        return new Response($content, 200, [
            'Content-Type' => 'json' === $format ? 'application/json' : 'application/xml',
        ]);
    }
}