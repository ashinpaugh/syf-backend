<?php

namespace Moop\Bundle\HealthBundle\Security\Authentication\Provider;

use Doctrine\Common\Util\Debug;
use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Loads a user from the database.
 * 
 * @author Austin Shinpaugh
 */
class ApiProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserService
     */
    protected $user_service;
    
    /**
     * @var UserPasswordEncoder
     */
    protected $encoder;
    
    /**
     * @var UserInterface
     */
    protected $user_cls;
    
    /**
     * Construct.
     * 
     * @param UserService         $service
     * @param UserPasswordEncoder $encoder
     * @param String              $user_cls
     */
    public function __construct(UserService $service, UserPasswordEncoder $encoder, $user_cls)
    {
        $this->user_service = $service;
        $this->encoder      = $encoder;
        $this->user_cls     = $user_cls;
    }
    
    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->getCredentials()) {
            return $this->loadUserByCredentials($token);
        }
        
        return $this->loadUserByHeader($token);
    }
    
    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiUserToken;
    }
    
    /**
     * 
     * 
     * @param TokenInterface $token
     *
     * @return ApiUserToken
     */
    protected function loadUserByCredentials(TokenInterface $token)
    {
        if (!$user = $this->getProvider()->getUser($token->getUsername())) {
            throw new AuthenticationException('Username not found.');
        }
        
        if (!$this->encoder->isPasswordValid($user, $token->getCredentials())) {
            throw new AuthenticationException('Password does not match.');
        }
        
        return new ApiUserToken($user, $user->getPassword(), 'api', $user->getRoles());
    }
    
    /**
     * 
     * 
     * @param TokenInterface $token
     *
     * @return ApiUserToken
     */
    protected function loadUserByHeader(TokenInterface $token)
    {
        $username = base64_decode($token->getUsername(), true);
        $user     = $this->getProvider()->getUser($username);
        
        if (get_class($user) !== $this->user_cls) {
            throw new AuthenticationException('Invalid credentials sent in header.');
        }
        
        return new ApiUserToken($user, $user->getPassword(), 'api', $user->getRoles());
    }
    
    /**
     * @return UserService
     */
    protected function getProvider()
    {
        return $this->user_service;
    }
}
