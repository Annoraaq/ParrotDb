<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Persistence\PMemoryDatabase;
use \ParrotDb\Persistence\XmlDatabase;

/**
 * Description of PSession
 *
 * @author J. Baum
 */
class PSession {
    
    const DB_MEMORY = 1;
    const DB_XML = 2;
    
    private $latestObjectId;
    
    private $filePath;
    
    private $database;
    
    public function __construct($filePath, $dbEngine) {
        $this->filePath = $filePath;
        $this->latestObjectId = 0;
        
        switch ($dbEngine) {
            case (self::DB_MEMORY):
                $this->database = new PMemoryDatabase();
                break;
            case (self::DB_XML):
                $this->database = new XmlDatabase();
                break;
            default:
                throw new PException(
                    "The given database engine could not be found."
                );
        }
        
    }
    
    public function createPersistenceManager() {
        return new PPersistanceManager($this);
    }
    
    public function assignObjectId() {
        $objectId = $this->latestObjectId;
        $this->latestObjectId++;
        return new PObjectId($objectId);
    }
    
    public function close() {
        PSessionFactory::closeSession($this->filePath);
    }
    
    public function getDatabase() {
        return $this->database;
    }
    
}
