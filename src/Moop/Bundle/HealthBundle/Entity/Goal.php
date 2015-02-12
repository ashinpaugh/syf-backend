<?php

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Goal extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var User
     */
    protected $user;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $description;
    
    /**
     * @ORM\Column(type="float")
     * @var Float
     */
    protected $max_points;
    
    /**
     * @ORM\Column(type="float")
     * @var Float
     */
    protected $point_progress;
    
    /**
     * @ORM\Column(type="boolean")
     * @var Boolean
     */
    protected $is_default;
    
    /**
     * {@inheritdoc}
     */
    protected function getSerializableProperties()
    {
        return [
            'id',
            'name',
            'description',
            'max_points',
            'point_progress',
            'is_default',
        ];
    }
}