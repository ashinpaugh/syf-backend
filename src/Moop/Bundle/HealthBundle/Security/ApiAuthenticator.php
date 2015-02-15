<?php

namespace Moop\Bundle\HealthBundle\Security;


use Doctrine\Common\Util\Debug;
use Monolog\Logger;
use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Security\Authentication\Provider\ApiProvider;
use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Moop\Bundle\HealthBundle\Service\ResponseManager;
use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiAuthenticator implements AuthenticationProviderInterface
{
    /**
     * @var ApiProvider
     */
    protected $provider;
    
    /**
     * @var UserPasswordEncoder
     */
    protected $encoder;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    
    public function __construct(ApiProvider $provider, UserPasswordEncoder $encoder, Logger $logger)
    {
        $this->provider = $provider;
        $this->encoder  = $encoder;
        $this->logger   = $logger;
        
        Debug::dump(['Authenticaator' => func_get_args()]);
    }
    
    /*public function authenticateToken(
        TokenInterface $token,
        UserProviderInterface $provider,
        $providerKey
    ) {
        $user = $provider->loadUserByUsername($token->getUsername());
        
        if (!$user instanceof User) {
            throw new AuthenticationException('User not found: ' . $token->getUsername());
        }
        
        return new ApiUserToken(
            $user,
            $user->getPassword(),
            $providerKey,
            $user->getRoles()
        );
    }
    
    public function createToken(
        Request $request,
        $username,
        $password,
        $providerKey
    ) {
        $this->logger->debug(print_r(['user' => $request->get('_username'), 'pass' => $request->get('_password')], 1));
        
        if (($user = $request->get('_username')) && ($pass = $request->get('_password'))) {
            //return new ApiUserToken($user, $pass, $providerKey);
            return new UsernamePasswordToken($user, $pass, $providerKey);
        }
        
        if (!$apiKey = $request->headers->get('X-AUTH-TOKEN')) {
            throw new BadCredentialsException('No API key found');
        }
        
        return new ApiUserToken(
            base64_decode($apiKey),
            '',
            $providerKey
        );
    }
    
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof ApiUserToken
            && $token->getProviderKey() === $providerKey;
    }*/
    
    
    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token)
    {
        if ($token->getCredentials()) {
            return $this->loadUserByCredentials($token);
        }
        
        return $this->loadUserByHeader($token);
    }
    
    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiUserToken;
    }
    
    protected function loadUserByCredentials(TokenInterface $token)
    {
        if (!$user = $this->provider->loadUserByUsername($token->getUsername())) {
            throw new AuthenticationException('Username not found.');
        }
        
        if (!$this->encoder->isPasswordValid($user, $token->getCredentials())) {
            throw new AuthenticationException('Password does not match.');
        }
        
        return new ApiUserToken($user, $user->getPassword(), 'api', $user->getRoles());
    }
    
    protected function loadUserByHeader(TokenInterface $token)
    {
        $username = base64_decode($token->getUsername());
        $user     = $this->provider->loadUserByUsername($username);
        
        if (!$user instanceof User) {
            throw new AuthenticationException('Invalid credentials sent in header.');
        }
        
        return new ApiUserToken($user, $user->getPassword(), 'api', $user->getRoles());
    }
}