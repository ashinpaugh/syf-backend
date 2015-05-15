<?php

namespace Moop\Bundle\HealthBundle\EventListener;

use Moop\Bundle\HealthBundle\Response\CorsResponse;
use Moop\Bundle\HealthBundle\Service\ResponseManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    
    public function onKernelError(GetResponseForExceptionEvent $event)
    {
        if (!$event->getRequest()->isXmlHttpRequest()) {
            return;
        }
        
        $event->setResponse(
            $this->buildResponse($event)
        );
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
        
        $request = $e->getRequest();
        
        // Handle the Preflight requests.
        if ($request->isMethod('OPTIONS')) {
            $e->setResponse($this->manager->handle($request));
            return;
        }
        
        // Parse the request body for params that Symfony doesn't notice.
        if ($this->handleAjaxRequest($request)) {
            return;
        }
        
        // TODO: Remember what belonged here...
    }
    
    /**
     * Add the user token ID to the headers if a valid session was found.
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
     * @param GetResponseEvent $event
     *
     * @return CorsResponse
     */
    private function buildResponse(GetResponseEvent $event)
    {
        $format = $event->getRequest()->get('_format');
        $result = '';
        
        if ($event instanceof GetResponseForControllerResultEvent) {
            $result = $event->getControllerResult();
        } elseif ($event instanceof GetResponseForExceptionEvent) {
            $result = [
                'code'    => $event->getException()->getCode(),
                'message' => $event->getException()->getMessage()
            ];
        }
        
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
    
    /**
     * Parses the Request body for JSON rather than the traditional query
     * string found in the message body.
     *
     * Ths is useful when developing a SPA with a framework like AngularJS.
     *
     * @param Request $request
     *
     * @return bool
     * @throws \HttpInvalidParamException
     */
    private function handleAjaxRequest(Request $request)
    {
        if ('json' !== $request->getContentType() || !$request->getContent()) {
            return false;
        }
        
        if (!$params = json_decode($request->getContent(), true)) {
            throw new \HttpInvalidParamException(
                'Unable to decode request params'
            );
        }
        
        $bag = $request->isMethod('POST') ? $request->request : $request->query;
        $bag->add($params);
        
        return true;
    }
}