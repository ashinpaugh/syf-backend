<?php

namespace Moop\Bundle\HealthBundle\Security;

use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var UserService
     */
    protected $service;
    
    
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
    
    /**
     * X-AUTH-TOKEN will be the user's username cause.. lazy.
     * 
     * @param String $api_key
     *
     * @return mixed
     */
    public function getUsernameForApiKey($api_key)
    {
        $username = base64_decode($api_key, true);
        return $username;
    }
    
    /**
     * Find a user based off their username and password.
     * 
     * @param String $username
     * @param String $password
     *
     * @return null|object
     */
    public function getUserByCredentials($username, $password)
    {
        return $this->service->getUser($username, $password);
    }
    
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->service->getUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'Moop\Bundle\HealthBundle\Entity\User' === $class;
    }
}