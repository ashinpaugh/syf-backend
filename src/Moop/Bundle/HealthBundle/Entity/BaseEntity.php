<?php

namespace Moop\Bundle\HealthBundle\Entity;


use Doctrine\Common\Util\Debug;

abstract class BaseEntity implements \Serializable, \JsonSerializable
{
    /**
     * Get the name's of the properties that can be serialized.
     * 
     * @return String[]
     */
    protected abstract function getSerializableProperties();
    
    /**
     * Get the array of values to serialize.
     * @return array
     */
    private function getValueArr()
    {
        $properties = [];
        foreach ($this->getSerializableProperties() as $name) {
            //echo "$name: $this->$name\n";
            $properties[$name] = $this->$name;
        }
        
        return $properties;
    }
    
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getValueArr();
    }
    
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->getValueArr());
    }
    
    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        $values = unserialize($data);
        
        foreach ($this->getSerializableProperties() as $name) {
            $this->$name = array_shift($values);
        }
    }
    
    private function serializeCollection($property)
    {
        
    }
}