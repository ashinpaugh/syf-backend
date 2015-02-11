<?php
/**
 * Created by PhpStorm.
 * User: ashinpaugh
 * Date: 2/8/15
 * Time: 8:59 AM
 */

namespace Moop\Bundle\HealthBundle\EventListener;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerListener
{
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        $event->setResponse(
            new JsonResponse($event->getControllerResult(), 200, array(
                'Content-Type'                     => 'application/json',
                'Access-Control-Allow-Origin'      => '*',
                'Access-Control-Allow-Methods'     => 'HEAD, GET, POST, PUT, DELETE, OPTIONS',
                //'Access-Control-Expose-Headers'    => '',
                //'Access-Control-Allow-Credentials' => 'true',
            ))
        );
    }
}