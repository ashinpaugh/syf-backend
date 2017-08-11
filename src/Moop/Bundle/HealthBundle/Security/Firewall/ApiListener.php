<?php

namespace Moop\Bundle\HealthBundle\Security\Firewall;


use Monolog\Logger;
use Moop\Bundle\HealthBundle\Security\Token\AbstractApiUserToken;
use Moop\Bundle\HealthBundle\Security\Token\JwtUserToken;
use Symfony\Component\HttpFoundation\Request;
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
    
    /**
     * @var String
     */
    protected $provider_key;
    
    /**
     * @var string
     */
    protected $algorithm;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        Logger $logger,
        $provider_key,
        $algorithm
    ) {
        $this->security     = $securityContext;
        $this->manager      = $authenticationManager;
        $this->logger       = $logger;
        $this->provider_key = $provider_key;
        $this->algorithm    = $algorithm;
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
        if (!$token = $this->getToken($request)) {
            return;
        }
        
        $this->security->setToken(
            $this->manager->authenticate($this->getToken($request))
        );
    }
    
    /**
     * @param Request $request
     *
     * @return AbstractApiUserToken
     */
    protected function getToken(Request $request)
    {
        return $request->headers->has('Authorization')
            ? $this->getSignedToken($request)
            : $this->getStandardToken($request)
        ;
    }
    
    /**
     * @param Request $request
     *
     * @return AbstractApiUserToken|null
     */
    private function getStandardToken(Request $request)
    {
        if (!$user = $request->get('_username')) {
            return null;
        }
        
        $this->logger->addDebug('STANDARD TOKEN');
        return $this->createToken($request, $request->get('_password'), $user);
    }
    
    /**
     * @param Request $request
     *
     * @return AbstractApiUserToken|null
     */
    private function getSignedToken(Request $request)
    {
        list($credentials) = sscanf(
            $request->headers->get('Authorization'),
            'Bearer %s'
        );
        
        $this->logger->addDebug('bearer: ' . $credentials);
        
        if (!$credentials) {
            return null;
        }
        
        $this->logger->addDebug('SIGNED TOKEN');
        return $this->createToken($request, $credentials);
    }
    
    /**
     * Build the token.
     * 
     * @param Request $request
     * @param string  $credentials
     * @param string  $username
     * 
     * @return JwtUserToken
     */
    private function createToken(Request $request, $credentials, $username = '')
    {
        return new JwtUserToken(
            $username,
            $credentials,
            $request->getHost(),
            $this->algorithm,
            $this->provider_key
        );
    }
}