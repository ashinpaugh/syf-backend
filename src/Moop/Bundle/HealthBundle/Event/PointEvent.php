<?php
/**
 * Created by PhpStorm.
 * User: ashinpaugh
 * Date: 4/20/15
 * Time: 5:16 PM
 */

namespace Moop\Bundle\HealthBundle\Event;


use Moop\Bundle\HealthBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class PointEvent extends Event
{
    protected $tag;
    protected $points;
    protected $user;
    protected $created_on;
    
    public function __construct($tag, $points, User $user)
    {
        $this->tag        = $tag;
        $this->points     = $points;
        $this->user       = $user;
        $this->created_on = new \DateTime();
    }
    
    public function getTag()
    {
        return $this->tag;
    }
    
    public function getPoints()
    {
        return $this->points;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function getCreatedOn()
    {
        return $this->created_on;
    }
}