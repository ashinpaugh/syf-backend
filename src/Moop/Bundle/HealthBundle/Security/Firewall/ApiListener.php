<?php

namespace Moop\Bundle\HealthBundle\Security\Firewall;


use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class ApiListener implements ListenerInterface
{
    /**
     * @var AuthenticationManagerInterface
     */
    protected $manager;
    
    /**
     * @var SecurityContext
     */
    protected $security;
    
    protected $provider_key;
    
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        $provider_key
    ) {
        $this->security = $securityContext;
        $this->manager  = $authenticationManager;
        $this->provider_key = $provider_key;
    }

    /**
     * Pass an unauthenticated token to the API Provider.
     * 
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        if (null !== $this->security->getToken()) {
            return;
        }
        
        $request = $event->getRequest();
        $user    = $request->get('_username', $request->headers->get('X-AUTH-TOKEN', ''));
        
        try {
            $token = $this->manager->authenticate(new ApiUserToken(
                $user,
                $request->get('_password'),
                $this->provider_key
            ));
            
            $this->security->setToken($token);

            return;
        } catch (AuthenticationException $failed) {}

        // By default deny authorization
        //$response = new Response();
        //$response->setStatusCode(403);
        //$event->setResponse($response);
    }
}