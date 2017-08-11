<?php

namespace Moop\Bundle\HealthBundle\Security\Encoder;

use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Encodes the user's X-AUTH-TOKEN for validation after the user
 * initially logs in.
 * 
 * This basic example is intended to be used as a model for future (more
 * comprehensive) implementations.
 * 
 * @author Austin Shinpaugh
 */
class ApiTokenEncoder implements ApiTokenEncoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encode(AbstractApiUserToken $token)
    {
        return $this->doEncode($token);
    }
    
    /**
     * {@inheritdoc}
     */
    public function decode(AbstractApiUserToken $token)
    {
        return $this->doDecode($token);
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate(AbstractApiUserToken $token, $credentials = null)
    {
        if ($this->doEncode($token) !== $credentials) {
            throw new AuthenticationException("The hash is invalid.");
        }
        
        $token->setAuthenticated(true);
        
        return true;
    }
    
    /**
     * Perform any "process intensive" work here.
     * 
     * @param ApiUserToken $token
     *
     * @return string
     */
    protected function doEncode(ApiUserToken $token)
    {
        return base64_encode($token->getUsername());
    }
    
    /**
     * Decode the string that was sent in the X-AUTH-TOKEN.
     * 
     * @param ApiUserToken $token
     *
     * @return string
     */
    protected function doDecode(ApiUserToken $token)
    {
        return base64_decode($token->getUsername(), true);
    }
}