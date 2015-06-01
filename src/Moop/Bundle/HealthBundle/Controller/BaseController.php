<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\FatSecretBundle\API\FatSecret;
use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Event\PointEvent;
use Moop\Bundle\HealthBundle\Event\PointEvents;
use Moop\Bundle\HealthBundle\Security\Token\ApiUserToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Moop\Bundle\HealthBundle\Entity\Goal;

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
     * Manually authorize a user that's using features under the null_sec firewall
     * so that they may be awarded points for using the app.
     * 
     * @param Request $request
     *
     * @throws \ErrorException
     */
    public function authorizeAndAward(Request $request)
    {
        if ($token = $this->doAuthorization($request)) {
            $this->updatePoints('search', $token->getUser());
        }
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
     * Manually authorization to override firewall definitions.
     * 
     * @param Request $request
     *
     * @return null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface|void
     */
    protected function doAuthorization(Request $request)
    {
        $security = $this->get('security.token_storage');
        if ($token = $security->getToken()) {
            return $token;
        }
        
        $token = null;
        if ($header = $request->headers->get('X-AUTH-TOKEN'))  {
            $token = new ApiUserToken($header, null, 'api');
        }
        
        if (!$token && $username = $request->request->get('_username')) {
            $token = new ApiUserToken(
                $username,
                $request->request->get('_password'),
                'api'
            );
        }
        
        if (!$token) {
            return null;
        }
        
        $token = $this->get('moop.fat_secret.security.provider.api')
            ->authenticate($token)
        ;
        
        $security->setToken($token);
        
        return $token;
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
