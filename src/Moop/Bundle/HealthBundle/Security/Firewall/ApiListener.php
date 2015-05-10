<?php

namespace Moop\Bundle\HealthBundle\Security\Firewall;


use Doctrine\Common\Util\Debug;
use Monolog\Logger;
use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
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
    
    /**
     * @var String
     */
    protected $provider_key;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        Logger $logger,
        $provider_key
    ) {
        $this->security     = $securityContext;
        $this->manager      = $authenticationManager;
        $this->logger       = $logger;
        $this->provider_key = $provider_key;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(GetResponseEvent $event)
    {
        if (null !== $this->security->getToken()) {
            return;
        }
        
        try {
            $this->attemptAuthentication($event->getRequest());
        } catch (AuthenticationException $failed) {
            $this->logger->debug('Authentication error:');
            $this->logger->debug(print_r($failed->getMessage(), 1));
            throw $failed;
        } catch (\Exception $failed) {
            $this->logger->debug('General error during authentication:');
            $this->logger->debug(print_r($failed->getMessage(), 1));
            throw $failed;
        }
    }
    
    /**
     * Authorize the user!
     *
     * @param Request $request
     */
    protected function attemptAuthentication(Request $request)
    {
        if (!$user = $request->get('_username', $request->headers->get('X-AUTH-TOKEN', ''))) {
            return;
        }
        
        $pass  = $request->get('_password');
        $token = new ApiUserToken($user, $pass, $this->provider_key);
        $token = $this->manager->authenticate($token);
        
        $this->security->setToken($token);
    }
}