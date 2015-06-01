<?php

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
class School extends BaseEntity implements \Serializable
{
    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="school")
     * 
     * @var User[]
     */
    protected $patrons;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * Constructor.
     */
    public function __construct()
    {
        $this->patrons = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getSerializableProperties()
    {
        return [
            'id',
            'name',
            'initials',
        ];
    }
    
    /**
     * @return User[]
     */
    public function getPatrons()
    {
        return $this->patrons;
    }
    
    /**
     * @param User $patron
     *
     * @return School
     */
    public function addPatron($patron)
    {
        $this->patrons->add($patron);
        
        if (!$patron->getSchool()) {
            $patron->setSchool($this);
        }
        
        return $this;
    }
    
    /**
     * @param User[] $patrons
     *
     * @return School
     */
    public function setPatrons($patrons)
    {
        $this->patrons->add($patrons);
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