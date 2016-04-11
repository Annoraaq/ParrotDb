<?php

namespace ParrotDb\Core;

/**
 * Description of Lock
 *
 * @author J. Baum
 */
class Lock
{
    private $path;
    
    /**
     * @param string $path Path to logfile
     */
    public function __construct($path) {
        $this->path = $path;
    }
    
    /**
     * Lock
     */
    public function lock() {
        $file = fopen($this->path, "w");
        $result = flock($file, LOCK_EX | LOCK_NB);
        fclose($file);
        
        return $result;
    }
    
    /**
     * Unlock
     */
    public function unlock() {
        $file = fopen($this->path, "w");
        flock($file, LOCK_UN);
        fclose($file);
    }
}
