<?php

namespace Moop\Bundle\HealthBundle\Service;


use Doctrine\ORM\EntityManager;
use Moop\Bundle\FatSecretBundle\API\FatSecret;
use Moop\Bundle\HealthBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

class UserService implements UserProviderInterface
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
     * @param EntityManager $manager
     * @param FatSecret     $api
     */
    public function __construct(EntityManager $manager, FatSecret $api, EncoderFactoryInterface $encoderFactory)
    {
        $this->doctrine        = $manager;
        $this->fs_api          = $api;
        $this->encoder_factory = $encoderFactory;
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
        
        $user
            ->setOauthToken($result['auth_token'])
            ->setOauthTokenSecret($result['auth_secret'])
        ;
        
        return $this;
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
        $new_salt  = $generator->nextBytes(10);
        $user->setSalt(base64_encode($new_salt));

        $encoder = $this->encoder_factory->getEncoder($user);
        $user->setPassword(
            $encoder->encodePassword($password, $user->getSalt())
        );

        return $this;
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
    
    
    
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->getRepository()->findOneBy([
            'username' => $username,
        ]);
        
        if ($user instanceof User) {
            return $user;
        }
        
        throw new UsernameNotFoundException(sprintf(
            'MoopHealthBundle:Service/UserProvider was unable to find username: %s',
            $username
        ));
    }
    
    /**
     * {@inheritdoc]
     */
    public function refreshUser(UserInterface $user)
    {
        if ($this->supportsClass($class = get_class($user))) {
            // ->initializeObject?
            $this->doctrine->refresh($user);
            
            return $user;
        }
        
        throw new UnsupportedUserException(sprintf(
            'Instances of "%s" are not supported.',
            $class
        ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $name = $this->getRepository()->getClassName();
        
        return $class === $name || is_subclass_of($class, $name);
    }
}