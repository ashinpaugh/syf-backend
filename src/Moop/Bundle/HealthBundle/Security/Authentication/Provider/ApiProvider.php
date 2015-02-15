<?php

namespace Moop\Bundle\HealthBundle\Security\Authentication\Provider;

use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Loads a user from the database.
 * 
 * @author Austin Shinpaugh
 */
class ApiProvider implements UserProviderInterface
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
    public function loadUserByUsername($username)
    {
        if (!$user = $this->user_service->getUser($username)) {
            throw new UsernameNotFoundException;
        }
        
        return new $user;
    }
    
    /**
     * The user was reloaded in {@see static::loadUserByUsername}
     * 
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }
    
    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $this->user_cls === $class;
    }
}
