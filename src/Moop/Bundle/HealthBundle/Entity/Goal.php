<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Moop\Bundle\HealthBundle\Entity\Repository\GoalRepository")
 * @ORM\Table(name="`goal`")
 * 
 * # Doctrine doesn't see the user column when creating the index for some reason.
 * # ORM\Index(name="idx_tag_user", columns={"tag", "user"})
 */
class Goal extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Moop\Bundle\HealthBundle\Entity\User", inversedBy="goals")
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
    protected $tag;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $description;
    
    /**
     * @ORM\Column(type="float")
     * @var Float
     */
    protected $total_points;
    
    /**
     * @ORM\Column(type="boolean")
     * @var Boolean
     */
    protected $is_default;
    
    /**
     * Points earned upon completing the goal.
     * 
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $bonus_pts;
    
    /**
     * @ORM\Column(type="smallint")
     * @var Int
     */
    protected $status;
    
    /**
     * Constructor.
     */
    public function __construct($name, $tag, $description, $total_points, $bonus, $is_default = false)
    {
        $this->points       = new ArrayCollection();
        $this->name         = $name;
        $this->tag          = $tag;
        $this->description  = $description;
        $this->total_points = $total_points;
        $this->bonus_pts    = $bonus;
        $this->is_default   = $is_default;
        $this->status       = 1;
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
            'is_default',
            'status',
            'tag',
            'total_points',
            'bonus_pts',
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
        
        return round($total / $this->total_points, 2);
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
    
    public function addPoints(Point $point)
    {
        $this->points->add($point);
    }
    
    /**
     * @param Integer $value
     *
     * @return Point
     */
    public function addNewPoint($value)
    {
        $point = new Point($this, $value);
        $this->points->add($point);
        
        return $point;
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
     * @return boolean
     */
    public function isDefault()
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
    
    /**
     * @return String
     */
    public function getTag()
    {
        return $this->tag;
    }
    
    /**
     * @param String $tag
     *
     * @return Goal
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        
        return $this;
    }
    
    /**
     * @return Float
     */
    public function getTotalPoints()
    {
        return $this->total_points;
    }
    
    /**
     * @param Float $total_points
     *
     * @return Goal
     */
    public function setTotalPoints($total_points)
    {
        $this->total_points = $total_points;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getBonusPts()
    {
        return $this->bonus_pts;
    }
    
    /**
     * @param int $bonus_pts
     *
     * @return Goal
     */
    public function setBonusPts($bonus_pts)
    {
        $this->bonus_pts = $bonus_pts;
        
        return $this;
    }
    
    /**
     * @return Int
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @param Int $status
     *
     * @return Goal
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;
        
        return $this;
    }
    
    public function toggleStatus()
    {
        $this->status = !$this->status;
        return $this;
    }
    
    public function isEnabled()
    {
        return $this->status === 1;
    }
}