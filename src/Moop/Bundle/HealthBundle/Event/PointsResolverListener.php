<?php

namespace Moop\Bundle\HealthBundle\Event;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Moop\Bundle\HealthBundle\Entity\Goal;
use Moop\Bundle\HealthBundle\Service\PointService;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

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
    
    /**
     * @var PointService
     */
    protected $service;
    
    public function __construct(EntityManager $manager, PointService $service, Logger $logger)
    {
        $this->doctrine = $manager;
        $this->service  = $service;
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
     * Store any pending points by triggering the point service.
     * 
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->service->doDispatch();
    }
    
    /**
     * 
     * @param Goal $goal
     */
    protected function checkForCompletion(Goal $goal)
    {
        if (!$goal->isComplete()) {
            return;
        }
        
        $this->doctrine->persist(
            $goal->addNewPoint($goal->getBonusPts())
        );
        
        $goal->toggleStatus();
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