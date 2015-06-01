<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Moop\Bundle\HealthBundle\Entity\Group;
use Moop\Bundle\HealthBundle\Entity\School;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/board")
 */
class BoardController extends BaseController
{
    /**
     * @Route("/leaders", methods={"GET"})
     */
    public function leadersAction()
    {
        /* @var QueryBuilder $builder */
        $builder = $this->getDoctrine()->createQueryBuilder();
        
        $builder
            ->select('
                u.id, u.username, u.display_name, SUM(p.value) AS total_points,
                s.id AS school_id, s.name AS school_name, s.initials AS school_initials
            ')
            ->from('MoopHealthBundle:User', 'u')
            ->join('u.school', 's')
            ->join('u.goals', 'goals')
            ->join('goals.points', 'p')
            ->groupBy('u.id')
            ->orderBy('total_points', 'DESC')
            ->setMaxResults(25)
        ;
        
        return $builder->getQuery()->getResult();
    }
    
    /**
     * @Route("/group")
     * @Method({"GET"})
     *
     * @return array
     */
    public function groupsAction()
    {
        /* @var QueryBuilder $builder */
        $builder = $this->getDoctrine()->createQueryBuilder();
        
        $builder
            ->select(
                'g.id AS group_id',
                'g.name AS display_name',
                'SUM(points.value) AS total_points'
            )
            ->from('MoopHealthBundle:Group', 'g')
            ->join('g.members', 'u')
            ->join('u.goals', 'goals')
            ->join('goals.points', 'points')
            ->where('goals.status = 1 AND goal.is_default = false')
            ->groupBy('g.id')
            ->orderBy('points.value')
        ;
        
        return $builder->getQuery()->getResult();
    }
    
    /**
     * @Route("/group/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param Group $group
     *
     * @return array
     */
    public function membersAction(Group $group)
    {
        /* @var QueryBuilder $builder */
        $builder = $this->getDoctrine()->createQueryBuilder();
        $builder
            ->select(
                'u.id',
                'u.username',
                'u.display_name',
                'u.email',
                'SUM(p.value) AS total_points',
                'COUNT(goal.id) AS total_goals'
            )
            ->from('MoopHealthBundle:Group', 'g')
            ->join('g.members', 'u')
            ->leftJoin('u.goals', 'goal')
            ->leftJoin('goal.points', 'p')
            ->where('g = :group')
            ->andWhere('goal.status = 1 AND goal.is_default = false')
            ->orderBy('total_points', 'DESC')
            ->setParameter('group', $group)
        ;
        
        return $builder->getQuery()->getResult();
    }
    
    /**
     * @Route("/school")
     * @Method({"GET"})
     *
     * @return array
     */
    public function schoolAction()
    {
        /* @var QueryBuilder $builder */
        $builder = $this->getDoctrine()->createQueryBuilder();
        $builder
            ->select(
                's.name',
                's.initials',
                'SUM(p.value) AS total_points',
                'COUNT(goal.id) AS total_goals'
            )
            ->from('MoopHealthBundle:School', 's')
            ->join('s.patrons', 'u')
            ->join('u.goals', 'goal')
            ->join('goal.points', 'p')
            ->where('goal.status = 1 AND goal.is_default = false')
            ->groupBy('s.id')
            ->orderBy('total_points', 'DESC')
        ;
        
        return $builder->getQuery()->getResult();
    }
    
    /**
     * @Route("/school/{id}", requirements={"id" = "\d+"})
     * @Method({"GET"})
     *
     * @param School $school
     *
     * @return array
     */
    public function schoolPatronsAction(School $school)
    {
        /* @var QueryBuilder $builder */
        $builder = $this->getDoctrine()->createQueryBuilder();
        $builder
            ->select(
                //'u.id, u.display_name, u.username, SUM(p.value) AS points',
                's.name',
                's.initials',
                'SUM(p.value) AS total_points',
                'COUNT(goal.id) AS total_goals'
            )
            ->from('MoopHealthBundle:School', 's')
            ->join('s.patrons', 'u')
            ->join('u.goals', 'goal')
            ->join('goal.points', 'p')
            ->where('u.school = :school')
            ->andWhere('goal.status = 1 AND goal.is_default = false')
            ->groupBy('s.id')
            //->addGroupBy('u.id')
            ->orderBy('total_points', 'DESC')
            ->setParameter('school', $school)
        ;
        
        return $builder->getQuery()->getResult();
    }
}