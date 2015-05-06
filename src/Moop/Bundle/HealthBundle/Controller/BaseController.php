<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\FatSecretBundle\API\FatSecret;
use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Event\PointEvent;
use Moop\Bundle\HealthBundle\Event\PointEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Moop\Bundle\HealthBundle\Entity\Goal;

class BaseController extends Controller
{
    /**
     * @return FatSecret
     */
    protected function getFatAPI()
    {
        return $this->container->get('moop.fat_secret.api');
    }
    
    /**
     * @param $repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($repository)
    {
        return $this->getDoctrine()->getRepository($repository);
    }
    
    /**
     * return ObjectManager
     */
    public function getDoctrine()
    {
        return parent::getDoctrine()->getManager();
    }
    
    /**
     * Add points when a user completes a task.
     *
     * @param String $tag
     * @param User   $user
     *
     * @throws \ErrorException
     */
    public function updatePoints($tag, User $user = null)
    {
        $user   = !$user instanceof User ? $this->getUser() : $user;
        $prefix = 'moop.event.points.';
        $points = $this->container->getParameter("{$prefix}{$tag}");
        
        if (!$points) {
            throw new \ErrorException('Points settings related to this activity were not found.');
        }
        
        $this->get('event_dispatcher')
            ->dispatch(PointEvents::ADD, new PointEvent($tag, $points, $user))
        ;
    }
}
