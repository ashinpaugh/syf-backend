<?php

namespace Moop\Bundle\HealthBundle\Service;


use Moop\Bundle\HealthBundle\Response\CorsResponse;
use Moop\Bundle\HealthBundle\Response\PreflightResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseManager
{
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Router
     */
    protected $router;
    
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    
    /**
     * @param Request $request
     * @param         $content
     * @param int     $status
     * @param array   $headers
     *
     * @return CorsResponse
     */
    public function handle(Request $request, $content = '', $status = 200, array $headers = [])
    {
        $this->request = $request;
        
        if ($this->request->isMethod('OPTIONS')) {
            return $this->getPreflightResponse($request, $content, $status);
        }
        
        return CorsResponse::create($content, $status, array_merge([
            'Access-Control-Allow-Origin'  => $this->getOrigin(),
        ], $headers));
    }
    
    /**
     * @param Request $request
     * @param String  $content
     * @param Integer $status
     *
     * @return $this
     */
    protected function getPreflightResponse(Request $request, $content, $status)
    {
        $name    = $this->request->attributes->get('_route');
        $route   = $this->router->getRouteCollection()->get($name);
        $methods = implode(', ', $route->getMethods());
        
        $headers = new ResponseHeaderBag([
            'Access-Control-Allow-Origin'  => $this->getOrigin(),
            'Access-Control-Allow-Methods' => $methods,
        ]);
        
        if ($request->headers->has('Access-Control-Request-Headers')) {
            $headers->set(
                'Access-Control-Allow-Headers',
                $request->headers->get('Access-Control-Request-Headers')
            );
        }
        
        // 'GET, POST, PUT, DELETE, PATCH, OPTIONS'
        return PreflightResponse::create($content, $status, $headers->allPreserveCase());
    }
    
    /**
     * @return string
     */
    private function getOrigin()
    {
        return $this->request->headers->get('Origin');
    }
}