<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Doctrine\ORM\Query\Expr\Join;
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
                'g.name AS group_name',
                'SUM(p.value) AS total_points',
                'COUNT(u.id) AS member_count',
                'COUNT(goal.id) AS goal_count'
            )
            ->from('MoopHealthBundle:Group', 'g')
            ->join('g.members', 'u')
            ->leftJoin('u.goals', 'goal')
            ->leftJoin('goal.points', 'p')
            //->having('member_count > 0')
            ->groupBy('g.id')
            ->orderBy('total_points', 'DESC')
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
            //->groupBy('u.id')
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
            ->groupBy('s.id')
            ->orderBy('total_points', 'DESC')
            ->setParameter('school', $school)
        ;
        
        return $builder->getQuery()->getResult();
    }
}