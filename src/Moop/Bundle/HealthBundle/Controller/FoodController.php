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
     * @Route("", requirements={"q" = "[a-zA-Z]+"})
     * @Method({"GET"})
     */
    public function searchAction($q, $page = 0)
    {
        return $this->getFatAPI()->searchFood($q, 15, $page);
    }
    
    /**
     * @Route("/{food_id}", requirements={"q" = "\d+"})
     * @Method({"GET"})
     */
    public function getAction(Request $request, $food_id)
    {
        return $this->getFatAPI()->getFood($food_id);
    }
}