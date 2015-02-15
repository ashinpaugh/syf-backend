<?php
/**
 * Created by PhpStorm.
 * User: ashinpaugh
 * Date: 2/12/15
 * Time: 12:02 AM
 */

namespace Moop\Bundle\HealthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/login")
 */
class LoginController extends BaseController
{
    /**
     * This action is intercepted by the Security bundle.
     * 
     * @Route("", name="login_verify")
     * @Method({"POST"})
     */
    public function verifyAction()
    {
        return $this->getUser();
    }
}