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
    
    public static function createSession($filePath, $dbEngine, $configPath = null) {

        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }

        $lock = new Lock($filePath . "/");

        if (!$lock->lock()) {
            throw new PException(
             "A Session to this database is already active: "
                . $filePath
            );
        }

        self::$locks[$filePath] = $lock;
        
        self::$sessions[$filePath] = new PSession($filePath, $dbEngine, $configPath);
        return self::$sessions[$filePath];
    }
    
    public static function closeSession($filePath) {
        self::$locks[$filePath]->release();
        unset(self::$sessions[$filePath]);
        unset(self::$locks[$filePath]);
    }
    
    
}
