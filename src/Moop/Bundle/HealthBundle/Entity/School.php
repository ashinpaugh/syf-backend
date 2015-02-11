<?php
/**
 * Created by PhpStorm.
 * User: ashinpaugh
 * Date: 2/8/15
 * Time: 10:21 AM
 */

namespace Moop\Bundle\HealthBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Moop\Bundle\HealthBundle\Entity\Repository\SchoolRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="school", indexes={
 *  @ORM\Index(name="idx_initials", columns={"initials"})
 * })
 */
class School
{
    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="school")
     * 
     * @var User[]
     */
    protected $patrons;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * 
     * @var String
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string")
     * 
     * @var String
     */
    protected $initials;
    
    /**
     * 
     */
    public function __construct()
    {
        $this->patrons = new ArrayCollection();
    }
    
    /**
     * @return User[]
     */
    public function getPatrons()
    {
        return $this->patrons;
    }
    
    /**
     * @param User[] $patron
     *
     * @return School
     */
    public function addPatrons($patron)
    {
        $this->patrons->add($patron);
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return School
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return School
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getInitials()
    {
        return $this->initials;
    }
    
    /**
     * @param String $initials
     *
     * @return School
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;
        
        return $this;
    }
    
    
}