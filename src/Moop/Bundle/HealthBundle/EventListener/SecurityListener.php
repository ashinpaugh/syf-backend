<?php

namespace Moop\Bundle\HealthBundle\EventListener;


use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class SecurityListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var Logger
     */
    protected $logger;
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->logger->notice(print_r(['success' => 1, 'token' => $token->getUsername()], 1));
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->logger->notice(print_r(['success' => 0, 'error' => $exception->getMessage()], 1));
    }
}