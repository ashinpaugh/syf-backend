<?php

namespace Moop\Bundle\HealthBundle\Service;


use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Moop\Bundle\FatSecretBundle\API\FatSecret;
use Moop\Bundle\HealthBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

class UserService
{
    /**
     * @var EntityManager
     */
    protected $doctrine;
    
    /**
     * @var FatSecret
     */
    protected $fs_api;
    
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoder_factory;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @param EntityManager $manager
     * @param FatSecret     $api
     */
    public function __construct(EntityManager $manager, FatSecret $api, EncoderFactoryInterface $encoderFactory, Logger $logger)
    {
        $this->doctrine        = $manager;
        $this->fs_api          = $api;
        $this->encoder_factory = $encoderFactory;
        $this->logger          = $logger;
    }
    
    /**
     * @param User $user
     *
     * @return $this
     */
    public function setFatOAuthTokens(User $user)
    {
        $result = $this->getApi()->createProfile($user->getUsername());
        
        if (!array_key_exists('auth_token', $result)) {
            // The account already exists on FatSecret - local db was probably wiped.
            $result = $this->getApi()->getAuthTokenInfo($user->getUsername());
        }
        
        $repository = $this->doctrine->getRepository('MoopFatSecretBundle:OAuthProvider');
        $provider   = $repository->findOneByName('fat_secret');
        
        $user->addOAuthToken(
            $provider,
            $result['auth_token'],
            $result['auth_secret']
        );
        
        return $this;
    }
    
    /**
     * Get a user by their username.
     * 
     * @param String $username
     * @param String $password
     *
     * @return null|object
     */
    public function getUser($username, $password = null)
    {
        if ($password) {
            $password = $this->encryptPassword($username);
        }
        
        return $this->getRepository()->findOneBy(array_filter([
            'username' => $username,
            'password' => $password,
        ]));
    }
    
    /**
     * @param String  $username
     * @param String  $email
     * @param Integer $student_id
     *
     * @return bool
     */
    public function checkOriginalCredentials($username, $email, $student_id)
    {
        return !$this->isUsernameTaken($username)
            && !$this->isEmailTaken($email)
            && !$this->isStudentIDTaken($student_id)
        ;
    }
    
    /**
     * @param String $username
     *
     * @return bool
     */
    public function isUsernameTaken($username)
    {
        $user = $this->getRepository()->findOneBy([
            'username' => $username,
        ]);
        
        return $user instanceof User;
    }
    
    /**
     * @param String $email
     *
     * @return bool
     */
    public function isEmailTaken($email)
    {
        $user = $this->getRepository()->findOneBy([
            'email' => $email,
        ]);
        
        return $user instanceof User;
    }
    
    /**
     * @param Integer $student_id
     *
     * @return bool
     */
    public function isStudentIDTaken($student_id)
    {
        $user = $this->getRepository()->findOneBy([
            'student_id' => $student_id,
        ]);
        
        return $user instanceof User;
    }
    
    /**
     * @param User   $user
     * @param String $password
     * 
     * @return $this
     */
    public function createPasswordHash(User $user, $password)
    {
        $generator = new SecureRandom();
        $salt      = base64_encode($generator->nextBytes(10));
        $password  = $this->getEncoder($user)->encodePassword($password, $salt);
        
        $user->setSalt($salt)->setPassword($password);
        
        return $password;
    }
    
    /**
     * @param String      $password
     * @param null|String $salt
     *
     * @return string
     */
    public function encryptPassword($password, $salt = null)
    {
        return $this->getEncoder()->encodePassword($password, $salt);
    }
    
    /**
     * Return the password encoder for the specified user class.
     * 
     * @param UserInterface|String $user
     *
     * @return \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    private function getEncoder($user = 'Moop\Bundle\HealthBundle\Entity\User')
    {
        return $this->encoder_factory->getEncoder($user);
    }
    
    
    
    /**
     * @return \Moop\Bundle\HealthBundle\Entity\Repository\UserRepository
     */
    private function getRepository()
    {
        return $this->doctrine->getRepository('MoopHealthBundle:User');
    }
    
    /**
     * @return FatSecret
     */
    private function getApi()
    {
        return $this->fs_api;
    }
}