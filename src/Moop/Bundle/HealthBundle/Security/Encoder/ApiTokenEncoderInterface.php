<?php

namespace Moop\Bundle\HealthBundle\Security\Encoder;

use Moop\Bundle\HealthBundle\Security\Token\AbstractApiUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface ApiTokenEncoderInterface
{
    /**
     * Encodes user metadata and returns a hash that can be checked later
     * to verify the user's credentials.
     *
     * The username must always be part of the encoded string.
     *
     * @param AbstractApiUserToken|TokenInterface $token
     *
     * @return String
     */
    public function encode(AbstractApiUserToken $token);
    
    /**
     * Decodes credentials and returns the embedded Username.
     *
     * @param AbstractApiUserToken|TokenInterface $token
     *
     * @return String
     */
    public function decode(AbstractApiUserToken $token);
    
    /**
     * Throws an error if the hash in the Request was invalid.
     *
     * @param AbstractApiUserToken|TokenInterface $token
     * @param String                              $credentials Optional. Either the original credentials
     *                                                         sent in the request or some other data
     *                                                         required to properly verify the sent hash.
     *
     * @return TokenInterface
     */
    public function authenticate(AbstractApiUserToken $token, $credentials = null);
}