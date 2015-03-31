<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Moop\Bundle\FatSecretBundle\API\FatSecret;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
}
