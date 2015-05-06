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
        $this->getUserData($eaten, $consumed, $burned);
        $this->updatePoints('login', $this->getUser());
        
        return [
            'user_meta' => $this->getUser(),
            'food_meta' => [
                'consumed'  => $consumed,
                'burned'    => $burned,
                'eaten_ids' => $eaten,
            ]
        ];
    }
    
    protected function getUserData(&$eaten, &$consumed, &$burned)
    {
        $api  = $this->getFatAPI();
        $days = round(time() / 86400);
        
        $results = $api
            ->setUserOAuthTokens($this->getUser())
            ->getFoodEntries(null, $days)
        ;
        
        $eaten    = [];
        $consumed = $burned = 0;
        
        if (!$results) {
            return;
        }
        
        $results = $results['food_entry'];
        
        if (!is_array($results)) {
            $this->parseEntry($results, $eaten, $consumed);
            return;
        }
        
        foreach ($results as $entry) {
            $this->parseEntry($entry, $eaten, $consumed);
        }
    }
    
    protected function parseEntry($entry, &$eaten, &$consumed)
    {
        $eaten[]   = $entry['food_id'];
        $consumed += $entry['calories'];
    }
}