<?php

namespace ParrotDb\ObjectModel;

/**
 * Description of PClass
 *
 * @author J. Baum
 */
class PClass{

    protected $name;
    
    protected $fields = array();
    
    protected $extent = array();
    
    protected $superclasses = array();
    
    public function __construct($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function addField($name) {
        $this->fields[$name] = $name;
    }
    
    public function getFields() {
        return $this->fields;
    }
    
    public function getExtent() {
        return $this->extent;
    }
    
    public function resetExtent() {
        $this->extent = array();
    }
    
    public function addExtentMember(PObjectId $id, $member) {
        $this->extent[$id->getId()] = $member;
    }
    
    public function isInExtent(PObjectId $id) {
        if (isset($this->extent[$id->getId()])) {
            return true;
        }
        
        return false;
    }
    
    public function addSuperclass($superclass) {
        $this->superclasses[$superclass] = $superclass;
    }
    
    public function getSuperclasses() {
        return $this->superclasses;
    }
    
    public function hasSuperclass($name) {
        return isset($this->superclasses[$name]);
    }

    
    public function hasField($name) {
        return isset($this->fields[$name]);
    }
    
    public function equals($object) {
        if (!($object instanceof PClass)) {
            return false;
        }
        
        if ($object->getName() != $this->getName()) {
            return false;
        }
        
        foreach ($this->getFields() as $field) {
            $found = false;
            foreach ($object->getFields() as $field2) {
                if ($field == $field2) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        
        foreach ($this->getSuperclasses() as $superclass) {
            $found = false;
            foreach ($object->getSuperclasses() as $superclass2) {
                if ($superclass == $superclass2) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        
        return true;
    }

}
