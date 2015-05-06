<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\HealthBundle\Response\StreamedCorsResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            throw new BadRequestHttpException('Missing required query param: q', null, 1);
        }
        
        $results = $this->getFatAPI()->searchFood(
            $query,
            $request->query->get('max_results', 15),
            $request->query->get('page', 0)
        );
        
        if ($results && ($user = $this->getUser())) {
            $this->updatePoints('search', $user);
        }
        
        return $results;
    }
    
    /**
     * @Route("/{food_id}", requirements={"food_id" = "\d+"})
     * @Method({"GET"})
     */
    public function getAction($food_id)
    {
        return $this->getFatAPI()->getFood($food_id);
    }
    
    /**
     * @Route("/{food_ids}", requirements={"\s+"})
     * @Method({"GET"})
     */
    public function batchAction(Request $request, $food_ids)
    {
        $food_ids = explode(',', $food_ids);
        return StreamedCorsResponse::create(function () use ($food_ids) {
            $this->printBatchFood($food_ids);
        });
    }
    
    /**
     * Stream the API results back to the requesting client as we get them.
     * 
     * @param Integer[] $food_ids
     */
    private function printBatchFood($food_ids)
    {
        $serializer = $this->get('serializer');
        $last_id    = end($food_ids);
        
        echo '[';
        foreach ($food_ids as $food_id) {
            $food    = $this->getFatAPI()->getFood($food_id);
            $content = json_decode(json_encode($food), true);
            $data    = $serializer->serialize($content, 'json');
            
            echo $data;
            echo $last_id === $food_id ? ']' : ',';
            
            ob_flush();
            flush();
        }
    }
    
    /**
     * Add food to your food diary.
     * 
     * @Route("/diary")
     * @Method({"GET", "POST"})
     */
    public function createFoodEntryAction(Request $request)
    {
        return $this->getFatAPI()
            ->setUserOAuthTokens($this->getUser())
            ->addFoodEntry(
                $request->get('id'),
                $request->get('serving_id'),
                $request->get('name'),
                $request->get('meal', 'other'),
                $request->get('portion', 1.0)
            )
        ;
    }
}