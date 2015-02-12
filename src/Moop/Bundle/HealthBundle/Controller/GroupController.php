<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/group")
 */
class GroupController extends BaseController
{
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function listAction()
    {
        $doctrine = $this->getRepository('MoopHealthBundle:Group');
        
        return $doctrine->findAll();
    }
}