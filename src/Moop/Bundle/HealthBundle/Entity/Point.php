<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
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
     * Construct.
     */
    public function __construct()
    {
        
    }
    
    /**
     * Get the name's of the properties that can be serialized.
     *
     * @return String[]
     */
    protected function getSerializableProperties()
    {
        return [
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
    
    
}