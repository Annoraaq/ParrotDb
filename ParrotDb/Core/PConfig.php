<?php

namespace ParrotDb\Core;


/**
 * Description of PConfig
 *
 * @author J. Baum
 */
class PConfig
{

    public $memoryLimit;
    public $activationDepth;
    
    public function __construct() {
        
        // default value is ~10 mb
        $this->memoryLimit = 10000000;
        
        // default is infinity
        $this->activationDepth = -1;
    }
}
