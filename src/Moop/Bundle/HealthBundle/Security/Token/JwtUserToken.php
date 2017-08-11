<?php

namespace Moop\Bundle\HealthBundle\Security\Token;

use Symfony\Component\Security\Core\User\UserInterface;

class JwtUserToken extends AbstractApiUserToken
{
    /**
     * @var array
     */
    protected $headers;
    
    /**
     * Construct.
     *
     * @param mixed  $user        The user object or username / id.
     * @param String $credentials The credentials the ApiTokenEncoder will manipulate.
     * @param String $host        The host the token was issued from.
     * @param String $algorithm   The algorithm used when encoding the JWT.
     * @param String $provider    Optional.
     * @param array  $roles       Optional.
     */
    public function __construct(
        $user,
        $credentials,
        $host,
        $algorithm   = 'HS256',
        $provider    = 'JWT',
        array $roles = array()
    ) {
        parent::__construct($user, $credentials, $provider, $roles);
        
        $this->id          = method_exists($user, 'getId') ? $user->getId() : uniqid();
        $this->issued      = time();
        $this->host        = $host;
        $this->valid_start = $this->issued - 1;
        $this->valid_stop  = $this->issued + 86400;
        $this->headers     = [
            'alg' => $algorithm,
            'typ' => 'JWT',
        ];
        
    }
    
    /**
     * Create a new instance.
     * 
     * @param String $credentials
     * @param String $host
     *
     * @return static
     */
    public static function create($credentials, $host)
    {
        return new static('', $credentials, $host);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getClaim()
    {
        return [
            'iat'  => time(),
            'jti'  => $this->getId(),
            'iss'  => $this->getHost(),
            'nbf'  => $this->getValidStart(),
            'exp'  => $this->getValidStop(),
            'data' => $this->getData(),
        ];
    }
    
    /**
     * Return the settings found in the token header.
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Return a setting found in the token header.
     * 
     * @return array
     */
    public function getHeader($header)
    {
        if (array_key_exists($header, $this->headers)) {
            return $this->headers[$header];
        }
        
        return null;
    }
    
    /**
     * Set a header value.
     * 
     * @param string $header
     * @param string $value
     *
     * @return $this
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
        
        return $this;
    }
    
    /**
     * {@inheritdoc]
     */
    public function getHash()
    {
        $parts = explode('.', $this->getCredentials());
        return $parts[2];
    }
}