<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\FatSecretBundle\API\FatSecret;
use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Security\Token\JwtUserToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    /**
     * @return FatSecret
     */
    protected function getFatAPI()
    {
        return $this->container->get('moop.fat_secret.api');
    }
    
    /**
     * @param $repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($repository)
    {
        return $this->getDoctrine()->getRepository($repository);
    }
    
    /**
     * return ObjectManager
     */
    public function getDoctrine()
    {
        return parent::getDoctrine()->getManager();
    }
    
    /**
     * Call the API.
     *
     * @param String $controller
     * @param array  $path
     * @param array  $query
     *
     * @return mixed
     */
    public function callApi($controller, array $path = [], array $query = [])
    {
        $result     = $this->forward($controller, $path, $query)->getContent();
        $serializer = $this->get('serializer');
        $request    = $this->get('request_stack')->getMasterRequest();
        
        return $serializer->decode($result, $request->get('_format'));
    }
    
    /**
     * Add points when a user completes a task.
     *
     * @param String $tag
     * @param User   $user
     *
     * @throws \ErrorException
     */
    public function updatePoints($tag, User $user = null)
    {
        $this->get('moop.health.service.points')->addTag(
            $tag,
            $user
        );
    }
    
    /**
     * Used for quick debugging.
     * 
     * @param mixed $item
     *
     * @return $this
     */
    protected function debug($item)
    {
        $str = $item;
        if (is_array($item) || is_object($item)) {
            $str = print_r($item, true);
        }
        
        $this->get('logger')->addDebug($str);
        return $this;
    }
}
