<?php

namespace ParrotDb\Core;

/**
 * Description of PSessionFactory
 *
 * @author J. Baum
 */
class PSessionFactory {
    
    private static $sessions = array();
    private static $locks = array();
    
    public static function createSession($filePath, $dbEngine) {
        
        self::$locks[$filePath] = new Lock($filePath);
        
        if (!self::$locks[$filePath]->lock()) {
            throw new PException(
             "A Session to this database is already active: "
                . $filePath
            );
        }
        
        self::$sessions[$filePath] = new PSession($filePath, $dbEngine);
        return self::$sessions[$filePath];
    }
    
    public static function closeSession($filePath) {
        self::$locks[$filePath]->unlock();
        unset(self::$sessions[$filePath]);
        unset(self::$locks[$filePath]);
    }
    
    
}
