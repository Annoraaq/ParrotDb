<?php

namespace ParrotDb\Core;

/**
 * Description of PSessionFactory
 *
 * @author J. Baum
 */
class PSessionFactory {
    
    private static $sessions = array();
    
    public static function createSession($filePath, $dbEngine) {
        if (!isset(self::$sessions[$filePath])) {
            self::$sessions[$filePath] = new PSession($filePath, $dbEngine);
            return self::$sessions[$filePath];
        } else {
            throw new PException("A Session to this database is already active.");
        }
    }
    
    public static function closeSession($filePath) {
        unset(self::$sessions[$filePath]);
    }
    
    
}
