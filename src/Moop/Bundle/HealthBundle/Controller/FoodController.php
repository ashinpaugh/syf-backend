<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    public function searchAction(Request $request)
    {
        if (!$query = $request->query->get('q')) {
            throw new BadRequestHttpException('Missing required query param: q');
        }
        
        return $this->getFatAPI()->searchFood(
            $query,
            $request->query->get('max_results', 15),
            $request->query->get('page', 0)
        );
    }
    
    /**
     * @Route("/{food_id}", requirements={"food_id" = "\d+"})
     * @Method({"GET"})
     */
    public function getAction(Request $request, $food_id)
    {
        return $this->getFatAPI()->getFood($food_id);
    }
}