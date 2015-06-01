<?php

namespace Moop\Bundle\HealthBundle\Security\Encoder;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface ApiTokenEncoderInterface
{
    /**
     * Encodes user metadata and returns a hash that can be checked later
     * to verify the user's credentials.
     * 
     * The username must always be part of the encoded string.
     * 
     * @param TokenInterface $token
     *
     * @return String
     */
    public function encode(TokenInterface $token);
    
    /**
     * Decodes credentials and returns the embedded Username.
     *
     * @param TokenInterface $token
     *
     * @return String
     */
    public function decode(TokenInterface $token);
    
    /**
     * Determines if the credentials provided were valid.
     * 
     * @param TokenInterface $token
     * @param String       $credentials
     *
     * @return Boolean
     */
    public function check(TokenInterface $token, $credentials);
}