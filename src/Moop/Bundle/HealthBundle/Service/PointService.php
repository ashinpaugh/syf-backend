<?php

namespace Moop\Bundle\HealthBundle\Service;


use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Event\PointEvent;
use Moop\Bundle\HealthBundle\Event\PointEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PointService
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    
    /**
     * @var TokenStorageInterface
     */
    protected $storage;
    
    /**
     * @var Int[]
     */
    protected $points;
    
    /**
     * @var PointEvent[]
     */
    protected $events;
    
    /**
     * Construct.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param TokenStorageInterface    $storage
     * @param array                    $points
     */
    public function __construct(EventDispatcherInterface $dispatcher, TokenStorageInterface $storage, array $points)
    {
        $this->events     = [];
        $this->dispatcher = $dispatcher;
        $this->storage    = $storage;
        $this->points     = $points;
    }
    
    /**
     * Add a tag that the user earned points for.
     * 
     * @param String $tag  The event tag.
     * @param User   $user Optional.
     *
     * @throws \ErrorException
     */
    public function addTag($tag, User $user = null)
    {
        if (!$points = $this->getPoint($tag)) {
            throw new \ErrorException('Unknown points tag: ' . $tag);
        }
        
        $this->events[] = new PointEvent($tag, $points, $user ?: $this->getUser());
    }
    
    /**
     * Dispatches the points earned for this session.
     */
    public function doDispatch()
    {
        foreach ($this->events as $event) {
            $this->dispatcher->dispatch(PointEvents::ADD, $event);
        }
    }
    
    /**
     * Get the point value associated with a given tag.
     * 
     * @param string $tag
     *
     * @return Int
     * @throws \ErrorException
     */
    protected function getPoint($tag)
    {
        if (array_key_exists($tag, $this->points)) {
            return $this->points[$tag];
        }
        
        return null;
    }
    
    /**
     * Get the user object.
     * 
     * return mixed
     */
    protected function getUser()
    {
        if ($this->storage->getToken()) {
            return $this->storage->getToken()->getUser();
        }
        
        return null;
    }
}