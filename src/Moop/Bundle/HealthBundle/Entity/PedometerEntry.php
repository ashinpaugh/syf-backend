<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="pedometer")
 */
class PedometerEntry extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Moop\Bundle\HealthBundle\Entity\User", inversedBy="entries")
     * @var User
     */
    protected $user;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * 
     * @var Int
     */
    protected $id;
    
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $started;
    
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $ended;
    
    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    protected $steps;
    
    /**
     * @ORM\Column(type="integer")
     * @var Int
     */
    protected $calories;
    
    /**
     * Constructor
     * 
     * @param User $user
     * @param Int  $started
     * @param Int  $ended
     * @param Int  $steps
     * @param Int  $calories
     */
    public function __construct(User $user, $started, $ended, $steps, $calories)
    {
        $this->user     = $user;
        $this->started  = new \DateTime("@" . $started);
        $this->ended    = new \DateTime("@" . $ended);
        $this->steps    = $steps;
        $this->calories = $calories;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getSerializableProperties()
    {
        return [
            'calories',
            'ended',
            'id',
            'started',
            'steps'
        ];
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
     * @return PedometerEntry
     */
    public function setUser($user)
    {
        $this->user = $user;
        
        return $this;
    }
    
    /**
     * @return Int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param Int $id
     *
     * @return PedometerEntry
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getStarted()
    {
        return $this->started;
    }
    
    /**
     * @param \DateTime $started
     *
     * @return PedometerEntry
     */
    public function setStarted($started)
    {
        $this->started = $started;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getEnded()
    {
        return $this->ended;
    }
    
    /**
     * @param \DateTime $ended
     *
     * @return PedometerEntry
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getSteps()
    {
        return $this->steps;
    }
    
    /**
     * @param mixed $steps
     *
     * @return PedometerEntry
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;
        
        return $this;
    }
    
    /**
     * @return Int
     */
    public function getCalories()
    {
        return $this->calories;
    }
    
    /**
     * @param Int $calories
     *
     * @return PedometerEntry
     */
    public function setCalories($calories)
    {
        $this->calories = $calories;
        
        return $this;
    }
}