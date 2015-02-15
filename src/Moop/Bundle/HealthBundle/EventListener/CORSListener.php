<?php

namespace Moop\Bundle\HealthBundle\EventListener;

use Doctrine\Common\Util\Debug;
use Monolog\Logger;
use Moop\Bundle\HealthBundle\Response\CORSResponse;
use Moop\Bundle\HealthBundle\Response\PreflightResponse;
use Moop\Bundle\HealthBundle\Service\ResponseManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Serializer;

class CORSListener
{
    /**
     * @var Serializer
     */
    protected $serializer;
    
    /**
     * @var SecurityContext
     */
    protected $security;
    
    protected $logger;
    
    /**
     * @var ResponseManager
     */
    protected $manager;
    
    public function __construct(SecurityContext $security, Serializer $serializer, Logger $logger, ResponseManager $manager)
    {
        $this->security   = $security;
        $this->serializer = $serializer;
        $this->logger     = $logger;
        $this->manager    = $manager;
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
            /*$response->headers->add(array_merge($headers, [
                'Access-Control-Allow-Origin' => '*',
            ]));*/
            
            $this->logger->debug('content:');
            $this->logger->debug($response->getContent());
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
        
        /*$e->setResponse(
            new Response('', 200, [
                'Access-Control-Max-Age'        => '3600',
                'Access-Control-Allow-Origin'   => '*',
                'Access-Control-Allow-Headers'  => 'Content-Type, X-AUTH-TOKEN',
                'Access-Control-Allow-Methods'  => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            ])
        );*/
        
        //$e->setResponse(PreflightResponse::create());
        $e->setResponse($this->manager->handleResponse($e->getRequest()));
    }
    
    /**
     * {@inheritdoc}
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        
        if (!($token = $this->security->getToken()) || !$token->getUser() instanceof UserInterface || !$token->isAuthenticated()) {
            return;
        }
        
        if ($event->getResponse() instanceof CORSResponse) {
            $event->getResponse()->addCustomHeaders([
                'X-AUTH-TOKEN' => base64_encode($token->getUsername()),
            ]);
        } else {
            $this->logger->alert("Not CORS Response!\n");
            $this->logger->alert($event->getResponse()->headers->allPreserveCase());
            $this->logger->alert(get_class($event->getResponse()));
            die();
        }
        
        /*$event->getResponse()->headers->add([
            'X-AUTH-TOKEN'                  => base64_encode($token->getUsername()),
            'Access-Control-Expose-Headers' => 'X-AUTH-TOKEN',
        ]);*/
    }
    
    /**
     * Format the response data into the appropriate format.
     * 
     * @param GetResponseForControllerResultEvent $event
     *
     * @return CORSResponse
     */
    private function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $format  = $event->getRequest()->get('_format');
        $result  = $event->getControllerResult();
        
        // Yea.. I'm not happy with it, but I'm tired of messing with it though.
        $content = json_decode(json_encode($result), true);
        //content = $this->replaceDates($content);
        $content = $this->serializer->serialize($content, $format);
        
        return $this->manager->handleResponse($event->getRequest(), $content, 200)
            ->setCustomHeader('X-AUTH-TOKEN', base64_encode($event->getRequest()->getUser()))
        ;
    }
    
    protected function replaceDates($data)
    {
        foreach ($data as $k => $v) {
            if (!$v instanceof \DateTime) {
                continue;
            }
            
            $data[$k] = $v->format('s');
        }
        
        return $data;
    }
}