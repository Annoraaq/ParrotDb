<?php

namespace ParrotDb\Persistence\Feather;

/**
 * This class is responsible for buffering data in memory to keep the access
 * times low.
 *
 * @author J. Baum
 */
class FeatherBufferManager
{

    private $buffer;
    private $wholeFileInBuffer;
    private $bufferOffset;

    /**
     * @param string $fileName
     * @return boolean
     */
    public function isWholeFileInBuffer($fileName) {
        if (isset($this->wholeFileInBuffer[$fileName])) {
            return $this->wholeFileInBuffer[$fileName];
        }
        
        return false;
    }
    
    /**
     * 
     * @param string $fileName
     * @param boolean $value
     */
    public function setWholeFileInBuffer($fileName, $value) {
        $this->wholeFileInBuffer[$fileName] = $value;
    }
    
    /**
     * Returns the amount of characters, which are in the buffer.
     * 
     * @param string $fileName
     * @return int
     */
    public function getBufferOffset($fileName) {
        if (!isset($this->bufferOffset[$fileName])) {
            $this->bufferOffset[$fileName] = 0;
        }
        
        return $this->bufferOffset[$fileName];
    }
    
    /**
     * Sets the amount of characters, which are in the buffer.
     * 
     * @param string $fileName
     * @param int $value
     */
    public function setBufferOffset($fileName, $value) {
        $this->bufferOffset[$fileName] = $value;
    }
    
    /**
     * @param string $fileName
     * @return array
     */
    public function getBuffer($fileName) {
        if (!isset($this->buffer[$fileName])) {
            $this->buffer[$fileName] = array();
           
        }
         return $this->buffer[$fileName];
    }
    
    /**
     * @param string $fileName
     * @param mixed $value
     */
    public function addToBuffer($fileName, $value) {
        $this->buffer[$fileName][] = $value;
    }
    
    /**
     * @param string $fileName
     */
    public function resetBuffer($fileName) {
        $this->bufferOffset[$fileName] = 0;
        $this->wholeFileInBuffer[$fileName] = false;
        unset($this->buffer[$fileName]);
    }

}
