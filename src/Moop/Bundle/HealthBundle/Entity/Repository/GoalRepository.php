<?php
/**
 * Created by PhpStorm.
 * User: ashinpaugh
 * Date: 4/20/15
 * Time: 5:26 PM
 */

namespace Moop\Bundle\HealthBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Moop\Bundle\HealthBundle\Entity\User;
use Moop\Bundle\HealthBundle\Entity\Goal;

class GoalRepository extends EntityRepository
{
    public function findByUserAndTag($user, $tag)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('g')
            ->from('MoopHealthBundle:Goal', 'g')
            ->where('g.user = :user OR g.user IS NULL')
            ->andWhere('g.tag = :tag AND g.status = 1')
            ->setParameters([
                'user' => $user,
                'tag'  => $tag,
            ])
        ;
        
        if (!$goal = $builder->getQuery()->getOneOrNullResult()) {
            return null;
        }
        
        if ($goal->getUser() instanceof User) {
            return $goal;
        }
        
        // It's a default goal.
        //$goal = $this->getEntityManager()->copy($goal);
        $this->getEntityManager()->detach($goal);
        
        $goal->setId(null);
        $goal->setUser($user);
        
        //$this->getEntityManager()->persist($goal);
        
        $this->getEntityManager()->persist($goal);
        $this->getEntityManager()->flush();
        
        return $goal;
    }
}