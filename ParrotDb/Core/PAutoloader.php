<?php

namespace ParrotDb\Core;

/**
 * @author J. Baum
 */
class PAutoloader {

    private $namespace;
    private $rootPath;
    
    public function __construct($namespace = null, $rootPath = '/') {
        $this->namespace = $namespace;
        $this->rootPath = $rootPath;
    }
    
    public function register() {
        spl_autoload_register(array($this, 'loadClass'));
    }
    
    public function loadClass($className) {
        
        if($this->namespace !== null) {
            $className = str_replace($this->namespace . '\\', '', $className);
        }

        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        
        $file = $this->rootPath . $className . '.php';

        
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
}
