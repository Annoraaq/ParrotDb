<?php

/**
 * Description of PrivateConstructor
 *
 * @author J. Baum
 */
class PrivateConstructor
{
    public $attr;
    
    private function __construct($attr)
    {
        $this->attr = $attr;
    }
    
    public function equals($object)
    {
        if (!($object instanceof PrivateConstructor)) {
            return false;
        }
        
        if ($this->attr != $object->attr) {
            return false;
        }
        
        return true;
    }
    
    public static function createObject($attr)
    {
        return new PrivateConstructor($attr);
    }

}
