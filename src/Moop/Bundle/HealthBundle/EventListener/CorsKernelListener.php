<?php

namespace Moop\Bundle\HealthBundle\EventListener;

use Monolog\Logger;
use Moop\Bundle\HealthBundle\Response\CorsResponse;
use Moop\Bundle\HealthBundle\Security\Encoder\ApiTokenEncoderInterface;
use Moop\Bundle\HealthBundle\Service\ResponseManager;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @var ApiTokenEncoderInterface
     */
    protected $encoder;
    
    protected $logger;
    
    /**
     * Constructor.
     *
     * @param SecurityContext          $security
     * @param Serializer               $serializer
     * @param ResponseManager          $manager
     * @param ApiTokenEncoderInterface $encoder
     */
    public function __construct(SecurityContext $security, Serializer $serializer, ResponseManager $manager, ApiTokenEncoderInterface $encoder, Logger $logger)
    {
        $this->security   = $security;
        $this->serializer = $serializer;
        $this->manager    = $manager;
        $this->encoder    = $encoder;
        $this->logger     = $logger;
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
        $event->setResponse($this->buildResponse($event));
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
        $this->handleAjaxRequest($request);
    }
    
    /**
     * Add the user token ID to the headers if a valid session was found.
     *
     * @param FilterResponseEvent $event
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
        $this->logger->addDebug($this->encoder->encode($token));
        /* @var CorsResponse $response */
        $response->addCustomHeaders([
            'Authorization' => $this->encoder->encode($token),
        ]);
    }
    
    /**
     * PHP does not include HTTP_AUTHORIZATION in the $_SERVER array, so this header is missing.
     * We retrieve it from apache_request_headers()
     *
     * @param HeaderBag $headers
     * @see http://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2
     */
    protected function fixAuthHeader(HeaderBag $headers)
    {
        if (!$headers->has('Authorization') && function_exists('apache_request_headers')) {
            $all = apache_request_headers();
            if (isset($all['Authorization'])) {
                $headers->set('Authorization', $all['Authorization']);
            }
        }
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
        
        // TODO: Find an elegant solution.
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
     * @throws BadRequestHttpException
     */
    private function handleAjaxRequest(Request $request)
    {
        $this->fixAuthHeader($request->headers);
        
        if ('json' !== $request->getContentType() || !$request->getContent()) {
            return false;
        }
        
        if (!$params = json_decode($request->getContent(), true)) {
            throw new BadRequestHttpException(
                'Unable to decode request params: '
                .  $request->getContent()
            );
        }
        
        $bag = $request->isMethod('POST') ? $request->request : $request->query;
        $bag->add($params);
        
        return true;
    }
}