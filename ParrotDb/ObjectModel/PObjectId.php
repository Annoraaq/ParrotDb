<?php


namespace ParrotDb\ObjectModel;

use \ParrotDb\Core\Comparable;

/**
 * Description of PObjectId
 *
 * @author J. Baum
 */
class PObjectId implements Comparable {
    
    private $id;
    
    public function __construct($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function equals($object) {
        if ($object instanceof PObjectId) {
            if ($object->getId() == $this->id) {
                return true;
            }
        }
        
        return false;
    }
    
    public function __toString() {
        if ($this->id == null) {
            return "null";
        }
        return "" . $this->id;
    }
    
}
