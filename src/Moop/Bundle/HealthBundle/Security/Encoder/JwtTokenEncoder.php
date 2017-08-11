<?php

namespace Moop\Bundle\HealthBundle\Security\Encoder;


use Monolog\Logger;
use Moop\Bundle\HealthBundle\Security\Encoder\Error\JwtHeaderParseError;
use Moop\Bundle\HealthBundle\Security\Encoder\Error\JwtInvalidInputError;
use Moop\Bundle\HealthBundle\Security\Token\AbstractApiUserToken;
use Moop\Bundle\HealthBundle\Security\Token\JwtUserToken;
use Moop\Bundle\HealthBundle\Util\Str;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;

class JwtTokenEncoder implements ApiTokenEncoderInterface
{
    /**
     * @var PasswordEncoderInterface
     */
    protected $encoder;
    protected $logger;
    
    /**
     * Construct.
     */
    public function __construct(PasswordEncoderInterface $encoder, Logger $logger)
    {
        $this->encoder = $encoder;
        $this->logger  = $logger;
    }
    
    /**
     * {@inheritdoc}
     */
    public function encode(AbstractApiUserToken $token)
    {
        if (!$token instanceof JwtUserToken) {
            throw new \ErrorException('Invalid token passed to JwtTokenEncoder.');
        }
        
        $content = Str::base64UrlEncode($token->getHeaders())
            . '.' . Str::base64UrlEncode($token->getClaim())
        ;
        
        // {@see Pbkdf2PasswordEncoder} Base64 encodes return value.
        $hash = $this->encoder->encodePassword(
            $content,
            $token->getUser()->getSalt()
        );
        
        return $content . '.' . Str::base64UrlEncode($hash);
    }
    
    /**
     * Decodes credentials and returns the embedded Username.
     *
     * @param AbstractApiUserToken|TokenInterface $token
     *
     * @return String
     */
    public function decode(AbstractApiUserToken $token)
    {
        $this->logger->addDebug("token encorder token: " . print_r($token, 1));
        if ($token->getUsername()) {
            return $token->getUsername();
        }
        
        if (!$this->validate($token)) {
            return null;
        }
        
        return $token->getUsername();
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate(AbstractApiUserToken $token, $credentials = null)
    {
        if (!$this->validateHash($token)) {
            throw new AuthenticationException("The hash is invalid. This could be an indication that it was tampered with.");
        }
        
        $token->setAuthenticated(true);
        
        return true;
    }
    
    /**
     * Validate the JWT Header & Body. Does NOT verify for a valid hash.
     *
     * @param AbstractApiUserToken $token
     *
     * @return bool
     * 
     * @throws JwtHeaderParseError
     * @throws JwtInvalidInputError
     */
    protected function validate(AbstractApiUserToken $token)
    {
        if (!$header = $this->parseHeader($token->getCredentials())) {
            throw new JwtHeaderParseError('Unable to parse credentials in header.');
        }
        
        if (!$this->validateHeaderKeys($header)) {
            throw new JwtHeaderParseError('Invalid or missing headers in JWT.');
        }
        
        $this->loadFromHeaderCredentials($token, $header);
        
        try {
            $this->validateInputs($token);
        } catch (AccountStatusException $e) {
            throw new JwtInvalidInputError($e->getMessage());
        }
        
        $token->setAttribute('parsed_header', $header);
        
        return true;
    }
    
    /**
     * The Base64 representation of the JWT.
     * 
     * @param String $credentials
     *
     * @return array|bool
     */
    private function parseHeader($credentials)
    {
        $this->logger->addDebug($credentials);
        $parts  = explode('.', $credentials);
        $header = [
            'algorithm' => '',
            'body'      => '',
            'hash'      => '',
        ];
        
        if (count($parts) < 3) {
            return false;
        }
        
        try {
            foreach ($header as $k => $part) {
                $value      = array_shift($parts);
                $header[$k] = Str::base64UrlDecode($value);
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return $header;
    }
    
    /**
     * Ensure the required token values are set.
     * 
     * @param String[] $header
     *
     * @return bool
     */
    private function validateHeaderKeys($header)
    {
        $required = [
            'algorithm' => ['alg', 'typ'],
            'body'      => ['iat', 'jti', 'iss', 'nbf', 'exp', 'data'],
        ];
        
        foreach ($required as $k => $required_keys) {
            $segment_keys = array_keys($header[$k]);
            
            if (array_diff($required_keys, $segment_keys)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Make sure the token has not been tampered with.
     * 
     * @param AbstractApiUserToken $token
     *
     * @return bool
     */
    private function validateInputs(AbstractApiUserToken $token)
    {
        $time = time();
        
        if ($token->getValidStart() && $token->getValidStart() > $time) {
            throw new CredentialsExpiredException("Invalid credential start date.");
        }
        
        if ($token->getValidStop() && $token->getValidStop() < $time) {
            throw new CredentialsExpiredException("Credentials have expired.");
        }
        
        return true;
    }
    
    /**
     * Load the JWT security token based off of the data in the header.
     * 
     * @param AbstractApiUserToken  $token
     * @param String[] $credentials
     */
    protected function loadFromHeaderCredentials(AbstractApiUserToken $token, $credentials)
    {
        $payload = $credentials['body'];
        
        $token
            ->setIssued($payload['iat'])
            ->setId($payload['jti'])
            ->setHost($payload['iss'])
            ->setValidStart($payload['nbf'])
            ->setValidStop($payload['exp'])
            ->setAttributes($payload['data'])
        ;
        
        $token->setUser($token->getAttribute('username'));
    }
    
    /**
     * Validate the hash.
     *
     * @param AbstractApiUserToken $token
     *
     * @return bool
     */
    private function validateHash(AbstractApiUserToken $token)
    {
        $hash_start = strrpos($token->getCredentials(), '.');
        $hash       = substr($token->getCredentials(), $hash_start + 1);
        $payload    = substr($token->getCredentials(), 0, $hash_start);
        
        return $this->encoder->isPasswordValid(
            $hash,
            $payload,
            $token->getUser()->getSalt()
        );
    }
}