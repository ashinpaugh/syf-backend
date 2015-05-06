<?php

namespace Moop\Bundle\HealthBundle\Event;


class PointEvents
{
    /**
     * Creating a new points entry which counts towards a user achieving a goal.
     */
    const ADD = 'moop.health.event.points.add';
    
    /**
     * Removing points from a user.
     * @TODO: Implement.
     */
    const REMOVE = 'moop.health.event.points.remove';
    
    /**
     * Gradually decrease the value of the points earned so that the leader board
     * can stay in flux to promote usage.
     */
    const DECAY = 'moop.health.event.points.decay';
}