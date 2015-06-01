<?php

namespace Moop\Bundle\HealthBundle\Security\Authentication\Provider;

use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Security\Encoder\ApiTokenEncoderInterface;
use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @var ApiTokenEncoderInterface
     */
    protected $token_encoder;
    
    /**
     * @var UserInterface
     */
    protected $user_cls;
    
    /**
     * Construct.
     *
     * @param UserService              $service
     * @param UserPasswordEncoder      $encoder
     * @param ApiTokenEncoderInterface $token_encoder
     * @param String                   $user_cls
     */
    public function __construct(UserService $service, UserPasswordEncoder $encoder, ApiTokenEncoderInterface $token_encoder, $user_cls)
    {
        $this->user_service  = $service;
        $this->encoder       = $encoder;
        $this->token_encoder = $token_encoder;
        $this->user_cls      = $user_cls;
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
        return $token instanceof ApiUserToken
            && 'api' === $token->getProviderKey()
        ;
    }
    
    /**
     * Loads a user based off of form credentials.
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
     * Loads the user based off of the X-AUTH-TOKEN header.
     * 
     * @param TokenInterface $token
     *
     * @return ApiUserToken
     */
    protected function loadUserByHeader(TokenInterface $token)
    {
        // The provider is called twice for some reason.
        if ($token->getUser() instanceof User) {
            if (!$token->isAuthenticated()) {
                $token->setAuthenticated(true);
            }
            
            return $token;
        }
        
        $username = $this->getTokenEncoder()->decode($token);
        $user     = $this->getProvider()->getUser($username);
        
        if (get_class($user) !== $this->user_cls) {
            throw new AuthenticationException('Invalid credentials sent in header.');
        }
        
        $api = new ApiUserToken($user, null, 'api', $user->getRoles());
        $api->setAttributes($token->getAttributes());
        $api->isAuthenticated(true);
        
        return $api;
    }
    
    /**
     * @return UserService
     */
    protected function getProvider()
    {
        return $this->user_service;
    }
    
    /**
     * @return ApiTokenEncoderInterface
     */
    protected function getTokenEncoder()
    {
        return $this->token_encoder;
    }
}
