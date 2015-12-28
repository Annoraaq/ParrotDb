<?php

/**
 * Description of Publication
 *
 * @author J. Baum
 */
class Publication {
    private $name;
    
    public function __construct($name = "") {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function equals($object) {
        if (!($object instanceof Publication)) {
            return false;
        }
        
        if ($this->name != $object->getName()) {
            return false;
        }
        
        return true;
    }
}
