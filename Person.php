<?php

use \Human;

/**
 * Description of Person
 *
 * @author J. Baum
 */
class Person extends Human {
    
    protected $nationality = "german";
    
    protected $bla = "blaBla";
    
    public function getNationality() {
        return $this->nationality;
    }
    
    public function getBla() {
        return $this->bla;
    }
    
    public function equals($object) {
        
        if (!parent::equals($object)) {
            return false;
        }
        
        if (!($object instanceof Person)) {
            return false;
        }
        
        if ($this->nationality != $object->getNationality()) {
            return false;
        }
        
        if ($this->bla != $object->getBla()) {
            return false;
        }

        
        return true;
    }
    
}
