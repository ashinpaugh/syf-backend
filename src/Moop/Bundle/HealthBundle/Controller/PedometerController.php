<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Routing\Annotation\Route;
use Moop\Bundle\HealthBundle\Entity\PedometerEntry;

/**
 * @Route("/entry")
 */
class PedometerController extends BaseController
{
    /**
     * @Route("/{started}/{ended}/{steps}/{calories}", methods={"POST"})
     * 
     * @param Int $started
     * @param Int $ended
     * @param Int $steps
     * @param Int $calories
     * 
     * @return array
     */
    public function addAction($started, $ended, $steps, $calories)
    {
        /*if (!$this->getUser()) {
            throw new AccessDeniedException();
        }*/
        
        $this->getDoctrine()->persist(
            new PedometerEntry($this->getUser(), $started, $ended, $steps, $calories)
        );
        
        $this->updatePoints('track', $this->getUser());
        $this->getDoctrine()->flush();
        
        return ['success' => 1];
    }
    
    /**
     * @Route("", methods={"GET"})
     * 
     * @return PedometerEntry[]
     */
    public function getAction()
    {
        if (!$this->getUser()) {
            throw new AccessDeniedException();
        }
        
        $builder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder()
            ->select('p')
            ->from('MoopHealthBundle:PedometerEntry', 'p')
            ->where('p.ended BETWEEN %1 AND %2')
            ->setParameters([
                new \DateTime(time() - 86400),
                new \DateTime()
            ])
        ;
        
        return $builder->getQuery()->getResult();
    }
}