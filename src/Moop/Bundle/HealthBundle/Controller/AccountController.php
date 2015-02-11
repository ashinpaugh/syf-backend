<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account")
 */
class AccountController extends BaseController
{
    /**
     * @Route("/create")
     * @Method({"POST"})
     */
    public function createAction()
    {
        
    }
}