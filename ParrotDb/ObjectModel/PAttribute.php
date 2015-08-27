<?php

namespace ParrotDb\ObjectModel;


/**
 * Description of PAttribute
 *
 * @author J. Baum
 */
class PAttribute {
    
    
    protected $name;
    
    protected $value;
    
    public function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setValue($value) {
        $this->value = $value;
    }
}
