<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Moop\Bundle\FatSecretBundle\API\FatUserInterface;
use Moop\Bundle\FatSecretBundle\Entity\OAuthProvider;
use Moop\Bundle\FatSecretBundle\Entity\OAuthToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Moop\Bundle\HealthBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="user", indexes={
 *  @ORM\Index(name="idx_student_id", columns={"student_id"}),
 *  @ORM\Index(name="idx_email", columns={"email"}),
 *  @ORM\Index(name="idx_name", columns={"first_name", "last_name"}),
 *  @ORM\Index(name="idx_user_type", columns={"type", "school_id"})
 * })
 */
class User extends BaseEntity implements UserInterface, FatUserInterface
{
    const STUDENT = 0;
    const FACULTY = 1;
    
    const LIMITED_FEATURES = 0;
    const FULL_FEATURES    = 1;
    const LBS_TO_KG        = 0.453592;
    
    /**
     * @ORM\ManyToOne(targetEntity="School", inversedBy="patrons")
     * 
     * @var School
     */
    protected $school;
    
    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="members")
     * @var Group[]
     */
    protected $groups;
    
    /**
     * @ORM\OneToMany(targetEntity="Moop\Bundle\HealthBundle\Entity\Goal", mappedBy="user")
     * @var Goal[]
     */
    protected $goals;
    
    /**
     * @ORM\ManyToMany(targetEntity="Moop\Bundle\FatSecretBundle\Entity\OAuthToken", cascade={"persist"})
     * @ORM\JoinTable(name="users_oauth_tokens",
     *    joinColumns={
     *      @ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *    inverseJoinColumns={
     *      @ORM\JoinColumn(name="oauth_token_id", referencedColumnName="id", unique=true)
     *    }
     * )
     * @var OAuthToken[]|ArrayCollection
     */
    protected $oauth_tokens;
    
    /**
     * @ORM\OneToMany(targetEntity="Moop\Bundle\HealthBundle\Entity\PedometerEntry", mappedBy="user")
     * @var PedometerEntry[]
     */
    protected $entries;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $username;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $password;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $salt;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $display_name;
    
    /**
     * @ORM\Column(name="student_id", type="bigint")
     * 
     * @var Integer
     */
    protected $student_id;
    
    /**
     * @ORM\Column(name="first_name", type="string")
     * 
     * @var String
     */
    protected $first_name;
    
    /**
     * @ORM\Column(name="last_name", type="string")
     * 
     * @var String
     */
    protected $last_name;
    
    /**
     * @ORM\Column(name="date_of_birth", type="date")
     * @var \DateTime
     */
    protected $date_of_birth;
    
    /**
     * Females = 0
     * Males   = 1
     * 
     * @ORM\Column(type="smallint")
     * @var Int
     */
    protected $sex;
    
    /**
     * @ORM\Column(name="date_created", type="datetime")
     * 
     * @var String
     */
    protected $date_created;
    
    /**
     * @ORM\Column(name="email", type="string")
     * @var String
     */
    protected $email;
    
    /**
     * @ORM\Column(type="smallint")
     * 
     * @var Integer
     */
    protected $type;
    
    /**
     * @ORM\Column(type="smallint")
     * 
     * @var Integer
     */
    protected $feature_set;
    
    /**
     * @ORM\Column(type="boolean")
     * @var Boolean
     */
    protected $is_active;
    
    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    protected $weight;
    
    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    protected $height;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->is_active    = true;
        $this->groups       = new ArrayCollection();
        $this->oauth_tokens = new ArrayCollection();
        $this->type         = static::STUDENT;
        $this->feature_set  = static::FULL_FEATURES;
        $this->date_created = new \DateTime();
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return static::STUDENT === $this->getType()
            ? ['ROLE_STUDENT']
            : ['ROLE_FACULTY'];
    }
    
    /**
     * @inheritDoc
     */
    public function eraseCredentials() { }
    
    /**
     * {@inheritdoc}
     */
    protected function getSerializableProperties()
    {
        return [
            'id',
            'username',
            'password',
            'email',
            'display_name',
            'student_id',
            'first_name',
            'last_name',
            'date_of_birth',
            'sex',
            'weight',
            'height',
            
            'date_created',
            'feature_set',
            'salt',
            'type',
            'is_active',
        ];
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getHiddenApiParams()
    {
        return [
            'password',
            'salt',
        ];
    }
    
    /**
     * @return School
     */
    public function getSchool()
    {
        return $this->school;
    }
    
    /**
     * @param School $school
     *
     * @return User
     */
    public function setSchool($school)
    {
        $this->school = $school;
        $school->addPatron($this);
        
        return $this;
    }
    
    /**
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
    
    /**
     * @param Group[] $groups
     *
     * @return User
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        
        return $this;
    }
    
    /**
     * @param Group $group
     *
     * @return User
     */
    public function addGroup(Group $group)
    {
        $this->groups->add($group);
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @param String $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * @param String $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getStudentId()
    {
        return $this->student_id;
    }
    
    /**
     * @param int $student_id
     *
     * @return User
     */
    public function setStudentId($student_id)
    {
        $this->student_id = $student_id;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getFirstName()
    {
        return $this->first_name;
    }
    
    /**
     * @param String $first_name
     *
     * @return User
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getLastName()
    {
        return $this->last_name;
    }
    
    /**
     * @param String $last_name
     *
     * @return User
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }
    
    /**
     * @param \DateTime $date_of_birth
     *
     * @return User
     */
    public function setDateOfBirth($date_of_birth)
    {
        if (!$date_of_birth instanceof \DateTime) {
            $this->date_of_birth = new \DateTime();
            $this->date_of_birth->setDate($date_of_birth, 1, 1);
        } else {
            $this->date_of_birth = $date_of_birth;
        }
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }
    
    /**
     * @param String $date_created
     *
     * @return User
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @param String $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param int $type
     *
     * @return User
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getFeatureSet()
    {
        return $this->feature_set;
    }
    
    /**
     * @param int $feature_set
     *
     * @return User
     */
    public function setFeatureSet($feature_set)
    {
        $this->feature_set = $feature_set;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getSalt()
    {
        return $this->salt;
    }
    
    /**
     * @param String $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        
        return $this;
    }
    
    /**
     * @return Int
     */
    public function getSex()
    {
        return $this->sex;
    }
    
    /**
     * @param Int $sex
     *
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->is_active;
    }
    
    /**
     * @param boolean $is_active
     *
     * @return User
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }
    
    /**
     * @param String $display_name
     *
     * @return User
     */
    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getGoals()
    {
        return $this->goals;
    }
    
    /**
     * @param mixed $goals
     *
     * @return User
     */
    public function setGoals($goals)
    {
        $this->goals = $goals;
        
        return $this;
    }
    
    
    /**
     * Get the OAuth token.
     *
     * @return OAuthToken[]|ArrayCollection
     */
    public function getOAuthTokens()
    {
        return $this->oauth_tokens;
    }
    
    /**
     *
     * @param mixed $provider
     *
     * @return String
     * @throws \ErrorException
     */
    public function getOAuthToken($provider)
    {
        $name = $provider instanceof OAuthProvider
            ? $provider->getName()
            : $provider
        ;
        
        foreach ($this->getOAuthTokens() as $token) {
            if ($name === $token->getProvider()->getName()) {
                return $token;
            }
        }
        
        throw new \ErrorException('Token not found.');
    }
    
    /**
     * Set the OAuth token.
     *
     * @param OAuthProvider $provider
     * @param String        $token
     * @param String        $secret
     *
     * @return $this
     */
    public function addOAuthToken(OAuthProvider $provider, $token, $secret)
    {
        $auth_token = new OAuthToken($provider, $token, $secret);
        
        $this->oauth_tokens->set($provider->getName(), $auth_token);
        $provider->addToken($auth_token);
        
        return $this;
    }
    
    /**
     * Remove a token that's associated with a user.
     *
     * @param OAuthToken $token
     *
     * @return $this
     */
    public function removeOAuthToken(OAuthToken $token)
    {
        $provider = $token->getProvider();
        $provider->removeToken($token);
        
        $this->oauth_tokens->remove($provider->getName());
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }
    
    /**
     * @param int $weight
     *
     * @return User
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }
    
    /**
     * @param mixed $height
     *
     * @return User
     */
    public function setHeight($height)
    {
        $this->height = $height;
        
        return $this;
    }
    
    public function getWeightInKg()
    {
        return $this->getWeight() * self::LBS_TO_KG;
    }
}