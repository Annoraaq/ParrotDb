<?php

namespace ParrotDb\Persistence\Feather;

/**
 * Description of FeatherBufferManager
 *
 * @author J. Baum
 */
class FeatherBufferManager
{

    private $buffer;
    private $wholeFileInBuffer;
    private $bufferOffset;

    public function isWholeFileInBuffer($className) {
        if (isset($this->wholeFileInBuffer[$className])) {
            return $this->wholeFileInBuffer[$className];
        }
        
        return false;
    }
    
    public function setWholeFileInBuffer($className, $value) {
        $this->wholeFileInBuffer[$className] = $value;
    }
    
    public function getBufferOffset($className) {
        if (!isset($this->bufferOffset[$className])) {
            $this->bufferOffset[$className] = 0;
        }
        
        return $this->bufferOffset[$className];
    }
    
    public function setBufferOffset($className, $value) {
        $this->bufferOffset[$className] = $value;
    }
    
    public function getBuffer($className) {
        if (!isset($this->buffer[$className])) {
            $this->buffer[$className] = array();
           
        }
         return $this->buffer[$className];
    }
    
    public function addToBuffer($className, $value) {
        $this->buffer[$className][] = $value;
    }
    
    public function resetBuffer($className) {
        $this->bufferOffset[$className] = 0;
        $this->wholeFileInBuffer[$className] = false;
        unset($this->buffer[$className]);
    }

}
