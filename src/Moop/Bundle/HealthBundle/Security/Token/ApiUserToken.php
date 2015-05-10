<?php

namespace Moop\Bundle\HealthBundle\Security\Token;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

class ApiUserToken extends AbstractToken
{
    protected $credentials;
    protected $provider_key;
    
    public function __construct($user, $credentials, $provider, array $roles = [])
    {
        parent::__construct($roles);
        
        $this->credentials  = $credentials;
        $this->provider_key = $provider;
        
        $this->setUser($user);
    }
    
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->credentials, $this->provider_key, parent::serialize()]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->credentials,
            $this->provider_key,
            $parent_val
        ) = unserialize($serialized);
        
        parent::unserialize($parent_val);
    }
    
    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();
        
        $this->credentials = null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
    
    /**
     * @return mixed
     */
    public function getProviderKey()
    {
        return $this->provider_key;
    }
    
    /**
     * @param mixed $provider_key
     *
     * @return ApiUserToken
     */
    public function setProviderKey($provider_key)
    {
        $this->provider_key = $provider_key;
        
        return $this;
    }
}