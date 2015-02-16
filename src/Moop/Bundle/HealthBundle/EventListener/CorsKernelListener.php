<?php

namespace Moop\Bundle\HealthBundle\EventListener;

use Moop\Bundle\HealthBundle\Response\CorsResponse;
use Moop\Bundle\HealthBundle\Service\ResponseManager;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Listens to incoming requests, adding headers and formatting responses
 * when necessary.
 * 
 * @author Austin Shinpaugh
 */
class CorsKernelListener
{
    /**
     * @var Serializer
     */
    protected $serializer;
    
    /**
     * @var SecurityContext
     */
    protected $security;
    
    /**
     * @var ResponseManager
     */
    protected $manager;
    
    /**
     * Constructor.
     * 
     * @param SecurityContext $security
     * @param Serializer      $serializer
     * @param ResponseManager $manager
     */
    public function __construct(SecurityContext $security, Serializer $serializer, ResponseManager $manager)
    {
        $this->security   = $security;
        $this->serializer = $serializer;
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
            $event->setResponse($this->buildResponse($event));
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
        if (!$e->isMasterRequest()) {
            return;
        }
        
        if ($e->getRequest()->isMethod('OPTIONS')) {
            $e->setResponse($this->manager->handle($e->getRequest()));
            return;
        }
        
        $request = $e->getRequest();
        if ('json' === $request->getContentType() && $content = $request->getContent()) {
            // Handle AngularJS service calls.
            $params = json_decode($content, true);
            
            $request->isMethod('POST')
                ? $request->request->add($params)
                : $request->query->add($params)
            ;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function onResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest() || $event->getRequest()->isMethod('OPTIONS')) {
            return;
        }
        
        if (!($token = $this->security->getToken()) || !$token->getUser() instanceof UserInterface) {
            return;
        }
        
        if (!($response = $event->getResponse()) instanceof CorsResponse) {
            return;
        }
        
        /* @var CorsResponse $response */
        $response->addCustomHeaders([
            'X-AUTH-TOKEN' => base64_encode($token->getUsername()),
        ]);
    }
    
    /**
     * Format the response data into the appropriate format.
     * 
     * @param GetResponseForControllerResultEvent $event
     *
     * @return CorsResponse
     */
    private function buildResponse(GetResponseForControllerResultEvent $event)
    {
        $format = $event->getRequest()->get('_format');
        $result = $event->getControllerResult();
        
        if (is_string($result)) {
            $result = ['message' => $result];
        }
        
        // Yea.. I'm not happy with it, but I'm tired of messing with it though.
        $content = json_decode(json_encode($result), true);
        $content = $this->serializer->serialize($content, $format);
        
        return $this->manager->handle(
            $event->getRequest(),
            $content,
            200
        );
    }
}