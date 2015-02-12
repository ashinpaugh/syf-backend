<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/school")
 */
class SchoolController extends BaseController
{
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function listAction()
    {
        $doctrine = $this->getRepository('MoopHealthBundle:School');
        
        return $doctrine->findAll();
    }
}