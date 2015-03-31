<?php

namespace Moop\Bundle\HealthBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="`group`", indexes={
 *  @ORM\Index(name="idx_name", columns={"name"})
 * })
 */
class Group extends BaseEntity
{
    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="groups")
     * 
     * @var User[]
     */
    protected $members;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $name;
    
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $date_created;
    
    
    /**
     * Constructor.
     * 
     * @param String $name
     */
    public function __construct($name)
    {
        $this->name         = $name;
        $this->members      = new ArrayCollection();
        $this->date_created = new \DateTime();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getSerializableProperties()
    {
        return [
            'id',
            'name',
            'date_created',
        ];
    }
    
    /**
     * @return User[]
     */
    public function getMembers()
    {
        return $this->members;
    }
    
    /**
     * @param User[] $members
     *
     * @return Group
     */
    public function setMembers($members)
    {
        $this->members = $members;
        
        return $this;
    }
    
    /**
     * @param User $user
     *
     * @return $this
     */
    public function addMember(User $user)
    {
        $this->members->add($user);
        $user->addGroup($this);
        
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
     * @return Group
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }
    
    /**
     * @param \DateTime $date_created
     *
     * @return Group
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        
        return $this;
    }
    
}