<?php

namespace Moop\Bundle\HealthBundle\Event;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Moop\Bundle\HealthBundle\Entity\Goal;

/**
 * Listens for events associated with users and their points.
 * 
 * @author Austin Shinpaugh
 */
class PointsResolverListener
{
    /**
     * @var EntityManager
     */
    protected $doctrine;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    public function __construct(EntityManager $manager, Logger $logger)
    {
        $this->doctrine = $manager;
        $this->logger   = $logger;
    }
    
    /**
     * The user performed an action - reward them for it.
     * It is assumed that all actions have an associated default goal.
     * 
     * @param PointEvent $event
     *
     * @throws \ErrorException
     */
    public function onAddPoints(PointEvent $event)
    {
        $goal = $this->getRelatedGoal($event);
        
        $this->doctrine->persist(
            $goal->addNewPoint($event->getPoints())
        );
        
        $this->checkForCompletion($goal);
        $this->doctrine->flush();
        
        $this->logger->addDebug('Awarded user ' . $event->getPoints() . ' points.');
    }
    
    /**
     * 
     * @param Goal $goal
     */
    protected function checkForCompletion(Goal $goal)
    {
        if (!$goal->isComplete()) {
            $goal->toggleStatus();
            $this->doctrine->flush($goal);
            return;
        }
        
        $this->doctrine->persist(
            $goal->addNewPoint($goal->getBonusPts())
        );
    }
    
    /**
     * @param PointEvent $event
     *
     * @return Goal
     * @throws \ErrorException
     */
    protected function getRelatedGoal(PointEvent $event)
    {
        $repo = $this->doctrine->getRepository('MoopHealthBundle:Goal');
        $goal = $repo->findByUserAndTag($event->getUser(), $event->getTag());
        
        if ($goal instanceof Goal) {
            $this->logger->addDebug('Found related goal: ' . $goal->getName());
            return $goal;
        }
        
        throw new \ErrorException('Unable to determine relevant goal.');
    }
}