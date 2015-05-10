<?php

namespace Moop\Bundle\HealthBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Moop\Bundle\HealthBundle\Entity\User;

class UserRepository extends EntityRepository
{
    public function getStepsTaken(User $user)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('SUM(p.steps) as total_steps')
            ->from('MoopHealthBundle:PedometerEntry', 'p')
            ->where('p.user = :user')
            ->andWhere('p.started BETWEEN :one AND :two')
            ->setParameters([
                'user' => $user,
                'one'  => new \DateTime('-1 day'),
                'two'  => new \DateTime('now')
            ])
        ;
        
        return $builder->getQuery()->getOneOrNullResult();
    }
    
    public function getTotalPoints(User $user)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select(
                'g, p',
                'SUM(p.value) as total_points'
            )
            ->from('MoopHealthBundle:Goal', 'g')
            ->join('g.points', 'p')
            ->where('g.user = :user')
            ->andWhere('p.goal = g')
            ->setParameters([
                'user' => $user,
            ])
        ;
        
        return $builder->getQuery()->getOneOrNullResult();
    }
}