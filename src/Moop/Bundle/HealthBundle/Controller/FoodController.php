<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/food")
 */
class FoodController extends BaseController
{
    /**
     * @Route("/search/{search}", defaults={"page": 0})
     * @Method({"GET"})
     */
    public function searchAction(Request $request, $search, $page)
    {
        return $this->getFatAPI()->searchFood($search, 15, $page);
    }
    
    /**
     * @Route("/{food_id}")
     * @Method({"GET"})
     */
    public function getAction(Request $request, $food_id)
    {
        return $this->getFatAPI()->getFood($food_id);
    }
}