<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/app")
 */
class AppController extends BaseController
{
    /**
     * Updates the user on some basic information as soon  as they open the app.
     * This has the benefit of making only one call.
     * 
     * @Route("/init")
     * @Method({"GET"})
     */
    public function initAction()
    {
        $response = [];
        $calls    = [
            'schools' => 'MoopHealthBundle:School:list',
            'groups'  => 'MoopHealthBundle:Group:list',
            'board'   => 'MoopHealthBundle:Board:leaders'
        ];
        
        foreach ($calls as $k => $call) {
            $response[$k] = $this->callApi($call);
        }
        
        return $response;
    }
}