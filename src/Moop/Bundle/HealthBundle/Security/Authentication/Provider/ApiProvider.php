<?php

namespace Moop\Bundle\HealthBundle\Security\Authentication\Provider;

use Monolog\Logger;
use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Security\Encoder\ApiTokenEncoderInterface;
use Moop\Bundle\HealthBundle\Security\Token\AbstractApiUserToken;
use Moop\Bundle\HealthBundle\Security\Token\JwtUserToken;
use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Loads a user from the database.
 * 
 * @author Austin Shinpaugh
 */
class ApiProvider implements AuthenticationProviderInterface, UserProviderInterface
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
     * @var String
     */
    protected $provider_key;
    
    /**
     * @var UserInterface
     */
    protected $user_cls;
    
    /**
     * @var String
     */
    protected $algorithm;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * Construct.
     *
     * @param UserService              $service
     * @param UserPasswordEncoder      $encoder
     * @param ApiTokenEncoderInterface $token_encoder
     * @param                          $key
     * @param String                   $user_cls
     */
    public function __construct(UserService $service, UserPasswordEncoder $encoder, ApiTokenEncoderInterface $token_encoder, $key, $user_cls, $algorithm, $logger)
    {
        $this->user_service  = $service;
        $this->encoder       = $encoder;
        $this->token_encoder = $token_encoder;
        $this->provider_key  = $key;
        $this->user_cls      = $user_cls;
        $this->algorithm     = $algorithm;
        
        $this->logger = $logger;
    }
    
    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->getUsername() && $token->getCredentials()) {
            $this->logger->addDebug('LOAD USER BY CREDENTIALS');
            return $this->loadUserByCredentials($token);
        }
        
        return $this->loadUserByHeader($token);
    }
    
    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        if ($token instanceof AbstractApiUserToken) {
            return true;
        }
        
        if (method_exists($token, 'getProviderKey')) {
            return $this->getKey() === $token->getProviderKey();
        }
        
        return false;
    }
    
    /**
     * Loads a user based off of form credentials.
     * 
     * @param TokenInterface $token
     *
     * @return AbstractApiUserToken
     */
    protected function loadUserByCredentials(TokenInterface $token)
    {
        if (!$user = $this->getProvider()->getUser($token->getUsername())) {
            throw new AuthenticationException('Username not found.');
        }
        
        if (!$this->encoder->isPasswordValid($user, $token->getCredentials())) {
            throw new AuthenticationException('Password does not match.');
        }
        
        return new JwtUserToken(
            $user,
            $user->getPassword(),
            $this->getKey(),
            $this->getAlgorithm(),
            $user->getRoles()
        );
    }
    
    /**
     * Loads the user based off of the X-AUTH-TOKEN header.
     * 
     * @param TokenInterface $token
     *
     * @return AbstractApiUserToken
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
        
        $token->setUser($user);
        $this->getTokenEncoder()->authenticate($token);
        
        return $token;
    }
    
    
    /**
     * Find the PHP notation of the JWT Algorithm notation.
     * ie: HS256 ~= sha256
     * 
     * @return string
     * @throws \ErrorException
     */
    protected function getAlgorithm()
    {
        if ('SH' === strtoupper(substr($this->algorithm, 0, 2))) {
            return 'HS' . filter_var(
                $this->algorithm,
                FILTER_SANITIZE_NUMBER_INT
            );
        }
        
        throw new \ErrorException('Invalid algorithm provided');
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
    
    /**
     * @return String
     */
    protected function getKey()
    {
        return $this->provider_key;
    }
    
    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $user = $this->getProvider()->getUser($username);
        
        if (!$user instanceof UserInterface) {
            return $user;
        }
        
        throw new UsernameNotFoundException('Username not found in API provider.');
    }
    
    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if ($user instanceof User) {
            return $user;
        }
        
        throw new UnsupportedUserException('API does n ot support this user obj.');
    }
    
    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->user_cls === $class;
    }
}
