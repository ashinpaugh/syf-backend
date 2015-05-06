<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity()
 * @ORM\Table(name="`point`")
 */
class Point extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Goal", inversedBy="points")
     * 
     * @var Goal
     */
    protected $goal;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     * 
     * @var Integer
     */
    protected $value;
    
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $created_on;
    
    /**
     * Construct.
     *
     * @param Goal $goal
     * @param int  $value
     */
    public function __construct(Goal $goal, $value)
    {
        $this->goal  = $goal;
        $this->value = $value;
    }
    
    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->setCreatedOn(new \DateTime());
    }
    
    /**
     * Get the name's of the properties that can be serialized.
     *
     * @return String[]
     */
    protected function getSerializableProperties()
    {
        return [
            'created_on',
            'id',
            'value',
        ];
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
     * @return Point
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @param int $value
     *
     * @return Point
     */
    public function setValue($value)
    {
        $this->value = $value;
        
        return $this;
    }
    
    /**
     * @return Goal
     */
    public function getGoal()
    {
        return $this->goal;
    }
    
    /**
     * @param Goal $goal
     *
     * @return Point
     */
    public function setGoal($goal)
    {
        $this->goal = $goal;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }
    
    /**
     * @param \DateTime $created_on
     *
     * @return Point
     */
    public function setCreatedOn($created_on)
    {
        $this->created_on = $created_on;
        
        return $this;
    }
}