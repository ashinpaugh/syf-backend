<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\HealthBundle\Response\CorsResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function verifyAction(Request $request)
    {
        $this->getUserData($request, $eaten, $consumed, $burned);
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
    
    protected function getUserData(Request $request, &$eaten, &$consumed, &$burned)
    {
        $api  = $this->getFatAPI();
        $days = round(time() / 86400);
        $user = $this->doAuthorization($request)->getUser();
        
        $results = $api
            ->setUserOAuthTokens($user)
            ->getFoodEntries(null, $days)
        ;
        
        $eaten    = [];
        $consumed = $burned = 0;
        
        if (!$results) {
            return;
        }
        
        $entries = $results['food_entry'];
        
        if (!is_numeric(key($entries))) {
            $this->parseEntry($entries, $eaten, $consumed);
            return;
        }
        
        $this->debug($results);
        
        foreach ($entries as $entry) {
            $this->parseEntry($entry, $eaten, $consumed);
        }
    }
    
    protected function parseEntry($entry, &$eaten, &$consumed)
    {
        $eaten[]   = $entry['serving_id'];
        $consumed += $entry['calories'];
    }
}