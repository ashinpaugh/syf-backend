<?php

namespace Moop\Bundle\HealthBundle\Security;


use Doctrine\Common\Util\Debug;
use Monolog\Logger;
use Moop\Bundle\HealthBundle\Response\CORSResponse;
use Moop\Bundle\HealthBundle\Service\ResponseManager;
use Moop\Bundle\HealthBundle\Service\UserService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var ResponseManager
     */
    protected $manager;
    
    public function __construct(ResponseManager $manager, Logger $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }
    
    /**
     * @param Request $request
     * @param String  $providerKey
     *
     * @return PreAuthenticatedToken|UsernamePasswordToken
     */
    public function createToken(Request $request, $providerKey)
    {
        //$attr = $request;
        //$request = Request::createFromGlobals();
        $this->logger->debug(print_r(['user' => $request->get('_username'), 'pass' => $request->get('_password')], 1));
        $this->logger->debug(print_r([
            'header'  => $request->getMethod(),
            //'is master' => $request->isMasterRequest(),
            'path'  => $request->getPathInfo(),
            'request params' => $request->request->all(),
            'attributes' => $request->attributes->all(),
        ], 1));
        if (($user = $request->get('_username')) && ($pass = $request->get('_password'))) {
            return new UsernamePasswordToken($user, $pass, $providerKey);
        }
        
        if (!$apiKey = $request->headers->get('X-AUTH-TOKEN')) {
            throw new BadCredentialsException('No API key found');
        }

        return new PreAuthenticatedToken('anon.', $apiKey, $providerKey);
    }
    
    /**
     * @param TokenInterface        $token
     * @param UserProviderInterface $provider
     * @param String                $provider_key
     *
     * @return PreAuthenticatedToken|UsernamePasswordToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $provider, $provider_key)
    {
        /* @var ApiKeyUserProvider $provider */
        if ($token instanceof UsernamePasswordToken) {
            return new UsernamePasswordToken(
                $user = $provider->loadUserByUsername($token->getUsername()),
                $token->getCredentials(),
                $provider_key,
                $user->getRoles()
            );
        }
        
        $api_key  = $token->getCredentials();
        $username = $provider->getUsernameForApiKey($api_key);

        if (!$username) {
            throw new AuthenticationException(
                sprintf('API Key "%s" does not exist.', $api_key)
            );
        }

        $user = $provider->loadUserByUsername($username);
        print_r([
            'username' => $user->getUsername(),
            'salt'     => $user->getSalt(),
        ]);
        
        return new PreAuthenticatedToken(
            $user,
            $api_key,
            $provider_key,
            $user->getRoles()
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return $this->manager->handleResponse($request, '', 200, [])
            ->setCustomHeader('X-AUTH-TOKEN', base64_encode($token->getUsername()))
        ;
        
        /*return CORSResponse::create('', 200, [
            'X-AUTH-TOKEN' => base64_encode($token->getUsername()),
        ]);*/
    }
    
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->manager->handleResponse($request, 'Authentication failed.', 403);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken
            || $token instanceof UsernamePasswordToken
            && $token->getProviderKey() === $providerKey;
    }
}