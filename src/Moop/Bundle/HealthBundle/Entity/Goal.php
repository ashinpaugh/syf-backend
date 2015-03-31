<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Goal extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="goals")
     * @var User
     */
    protected $user;
    
    /**
     * @ORM\OneToMany(targetEntity="Point", mappedBy="goal")
     * @var Point[]
     */
    protected $points;
    
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
     * @ORM\Column(type="string")
     * @var String
     */
    protected $description;
    
    /**
     * @ORM\Column(type="float")
     * @var Float
     */
    protected $value;
    
    /**
     * @ORM\Column(type="boolean")
     * @var Boolean
     */
    protected $is_default;
    
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->points = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getSerializableProperties()
    {
        return [
            'id',
            'name',
            'description',
            'value',
            'point_progress',
            'is_default',
        ];
    }
    
    /**
     * @return float
     */
    public function getCompletionPercentage()
    {
        $total = 0;
        
        foreach ($this->points as $point) {
            $total += $point->getValue();
        }
        
        return round($total / $this->value, 2);
    }
    
    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->getCompletionPercentage() >= 1;
    }
    
    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @param User $user
     *
     * @return Goal
     */
    public function setUser($user)
    {
        $this->user = $user;
        
        return $this;
    }
    
    /**
     * @return Point[]
     */
    public function getPoints()
    {
        return $this->points;
    }
    
    /**
     * @param Point[] $points
     *
     * @return Goal
     */
    public function setPoints($points)
    {
        $this->points = $points;
        
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
     * @return Goal
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
     * @return Goal
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param String $description
     *
     * @return Goal
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * @return Float
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @param Float $value
     *
     * @return Goal
     */
    public function setValue($value)
    {
        $this->value = $value;
        
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isIsDefault()
    {
        return $this->is_default;
    }
    
    /**
     * @param boolean $is_default
     *
     * @return Goal
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
        
        return $this;
    }
    
    
}