<?php

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
        $days = time() / 86400;
        //$user = $this->container->get('security.context')->getToken();
        //$api  = $this->getFatAPI()->setUserOAuthTokens($this->getUser());
        
        return [
            'user_meta' => $this->getUser(),
            //'food'      => $api->getFoodEntries(null, round($days))
        ];
    }
}