<?php

namespace Moop\Bundle\HealthBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Moop\Bundle\HealthBundle\Entity\User;

class SchoolRepository extends EntityRepository
{
    /**
     * @return User[]
     */
    public function getFaculty($school_id)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->from('user', 'u')
            ->innerJoin('school', 's')
            ->where('s.id = :id')
            ->andWhere('u.type = :type')
            ->setParameter(':id', $school_id)
            ->setParameter(':type', User::FACULTY)
        ;
        
        return $builder->getQuery()->getResult();
    }
}