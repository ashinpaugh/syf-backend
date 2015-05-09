<?php

namespace Moop\Bundle\HealthBundle\Entity;

/**
 * The base class for API entities.
 * 
 * @author Austin Shinpaugh
 */
abstract class BaseEntity implements \Serializable, \JsonSerializable
{
    /**
     * Get the name's of the properties that can be serialized.
     * 
     * @return String[]
     */
    protected abstract function getSerializableProperties();
    
    /**
     * @return \String[]
     */
    protected function getHiddenApiParams()
    {
        return [];
    }
    
    /**
     * If there are values that should not be shared with a client API.
     * 
     * @return \String[]
     */
    protected function stripApiParams()
    {
        return array_diff(
            $this->getSerializableProperties(),
            $this->getHiddenApiParams()
        );
    }
    
    /**
     * Get the array of values to serialize.
     * 
     * @param Boolean $api_safe
     * 
     * @return array
     */
    private function serializableValues($api_safe = false)
    {
        $params     = $api_safe ? $this->stripApiParams() : $this->getSerializableProperties();
        $properties = [];
        
        foreach ($params as $name) {
            $properties[$name] = $this->$name;
            
            if ($api_safe && $this->$name instanceof \DateTime) {
                $properties[$name] = $this->$name->format('U') * 1000;
            }
        }
        
        return $properties;
    }
    
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->serializableValues(true);
    }
    
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->serializableValues());
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
}