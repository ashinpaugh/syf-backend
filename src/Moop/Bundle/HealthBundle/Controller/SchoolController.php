<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\HealthBundle\Entity\School;
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
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function patronsAction(School $school)
    {
        return $school->getPatrons();
    }
}