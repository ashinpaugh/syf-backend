<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\HealthBundle\Response\StreamedCorsResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

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
        
        if ($user = $this->getUser()) {
            $this->updatePoints('search', $user);
        }
        
        return $results;
    }
    
    /**
     * @Route("/history", methods={"GET"})
     * 
     * @param Request $request
     *
     * @return array
     * @throws \Moop\Bundle\FatSecretBundle\Exception\FatException
     */
    public function getMealHistory(Request $request)
    {
        if (!$username = $request->query->get('q')) {
            throw new MissingMandatoryParametersException();
        }
        
        $user   = $this->get('moop.health.user.service')->getUser($username);
        $now    = new \DateTime();
        $output = [];
        
        for ($i = 7; $i > 0; $i--) {
            $result = $this->getFatAPI()
                ->setUserOAuthTokens($user)
                ->getFoodEntries(null, $now) //->format('U')
            ;
            
            if ($value = $result['food_entry']) {
                $output[] = $value;
            }
            
            $now = $now->modify("-{$i} days");
        }
        
        return $output;
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
     * Add food to your food diary.
     * 
     * @Route("/diary")
     * @Method({"GET", "POST"})
     */
    public function createFoodEntryAction(Request $request)
    {
        $this->updatePoints('track', $this->getUser());
        
        $day = floor(time() / 86400);
        
        return $this->getFatAPI()
            ->setUserOAuthTokens($this->getUser())
            ->addFoodEntry(
                $request->get('id'),
                $request->get('serving_id'),
                $request->get('name'),
                $request->get('meal', 'other'),
                $request->get('portion', 1.0),
                $day
            )
        ;
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
}