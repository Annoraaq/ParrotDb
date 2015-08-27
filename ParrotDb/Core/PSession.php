<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Persistence\PMemoryDatabase;

/**
 * Description of PSession
 *
 * @author J. Baum
 */
class PSession {
    
    private $latestObjectId;
    
    private $filePath;
    
    private $database;
    
    public function __construct($filePath) {
        $this->filePath = $filePath;
        $this->latestObjectId = 0;
        $this->database = new PMemoryDatabase();
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
