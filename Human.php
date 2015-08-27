<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Human
 *
 * @author J. Baum
 */
class Human {
    
    private $testAttribute = "test";
    
    public function getTestAttribute() {
        return $this->testAttribute;
    }
    
    public function equals($object) {
        if (!($object instanceof Human)) {
            return false;
        }
        
        if ($this->testAttribute != $object->getTestAttribute()) {
            return false;
        }
        
        return true;
    }
    

}
