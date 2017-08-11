<?php

namespace Moop\Bundle\HealthBundle\Security\Token;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A authentication token based around stateless sessions.
 *
 * Used by the ApiProvider and can be extended for integrating multiple
 * authentication methods at a time.
 * 
 * @author Austin Shinpaugh
 */
abstract class AbstractApiUserToken extends AbstractToken
{
    protected $id;
    protected $issued;
    protected $host;
    protected $valid_start;
    protected $valid_stop;
    protected $payload;
    protected $credentials;
    protected $provider_key;
    
    /**
     * Construct.
     * 
     * @param mixed  $user
     * @param mixed  $credentials
     * @param string $provider
     * @param array  $roles
     */
    public function __construct($user, $credentials, $provider, array $roles = [])
    {
        parent::__construct($roles);

        $this->credentials  = $credentials;
        $this->provider_key = $provider;
        
        $this->setUser($user);
        $this->setAuthenticated(count($roles) > 0);
    }
    
    /**
     * Returns the "body" of the token.
     * 
     * @return string[]
     */
    public abstract function getClaim();
    
    /**
     * Return the hash.
     * 
     * @return string
     */
    public function getHash()
    {
        return '';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        if ($this->getUser() instanceof UserInterface) {
            if (!parent::getRoles()) {
                parent::__construct($this->getUser()->getRoles());
            }
        }
        
        return parent::getRoles();
    }
    
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->credentials,
            $this->provider_key,
            parent::serialize()
        ]);
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
     * @return String
     */
    public function getProviderKey()
    {
        return $this->provider_key;
    }
    
    /**
     * @param mixed $provider_key
     *
     * @return $this
     */
    public function setProviderKey($provider_key)
    {
        $this->provider_key = $provider_key;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getIssued()
    {
        return $this->issued;
    }
    
    /**
     * @param mixed $issued
     *
     * @return $this
     */
    public function setIssued($issued)
    {
        $this->issued = $issued;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * @param mixed $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getValidStart()
    {
        return $this->valid_start;
    }
    
    /**
     * @param mixed $valid_start
     *
     * @return $this
     */
    public function setValidStart($valid_start)
    {
        $this->valid_start = $valid_start;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getValidStop()
    {
        return $this->valid_stop;
    }
    
    /**
     * @param mixed $valid_stop
     *
     * @return $this
     */
    public function setValidStop($valid_stop)
    {
        $this->valid_stop = $valid_stop;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getData()
    {
        if ($this->payload || !$this->getUser() instanceof UserInterface) {
            return $this->payload;
        }
        
        return [
            'id'       => $this->getUser()->getId(),
            'username' => $this->getUser()->getUsername(),
        ];
    }
    
    /**
     * @param string[] $payload
     *
     * @return $this
     */
    public function setData(array $payload)
    {
        $this->payload = $payload;
        
        return $this;
    }
    
    /**
     * Add a value to the payload.
     * 
     * @param String $key
     * @param mixed  $value
     * 
     * @return $this
     */
    public function addData($key, $value)
    {
        $this->payload[$key] = $value;
        return $this;
    }
}