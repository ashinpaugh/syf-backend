<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Moop\Bundle\HealthBundle\Entity\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user", indexes={
 *  @ORM\Index(name="idx_student_id", columns={"student_id"}),
 *  @ORM\Index(name="idx_name", columns={"first_name", "last_name"}),
 *  @ORM\Index(name="idx_oauth", columns={"oauth_token", "oauth_token_secret"})
 * })
 */
class User
{
    const STUDENT = 0;
    const FACULTY = 1;
    
    const LIMITED_FEATURES = 0;
    const FULL_FEATURES    = 1;
    
    /**
     * @ORM\ManyToOne(targetEntity="School", inversedBy="patrons")
     * 
     * @var School
     */
    protected $school;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="bigint")
     * 
     * @var Integer
     */
    protected $id;
    
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
     * @var Boolean
     */
    protected $feature_set;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $oauth_token;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $oauth_token_secret;
    
    /**
     * 
     */
    public function __construct()
    {
        
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
        $this->date_of_birth = $date_of_birth;
        
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
     * @return boolean
     */
    public function isFeatureSet()
    {
        return $this->feature_set;
    }
    
    /**
     * @param boolean $feature_set
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
    public function getOauthToken()
    {
        return $this->oauth_token;
    }
    
    /**
     * @param String $oauth_token
     *
     * @return User
     */
    public function setOauthToken($oauth_token)
    {
        $this->oauth_token = $oauth_token;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getOauthTokenSecret()
    {
        return $this->oauth_token_secret;
    }
    
    /**
     * @param String $oauth_token_secret
     *
     * @return User
     */
    public function setOauthTokenSecret($oauth_token_secret)
    {
        $this->oauth_token_secret = $oauth_token_secret;
        
        return $this;
    }
    
    
}