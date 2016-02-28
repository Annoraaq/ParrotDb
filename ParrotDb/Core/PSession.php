<?php

namespace ParrotDb\Core;

use \ParrotDb\Persistence\PMemoryDatabase;
use \ParrotDb\Persistence\XmlDatabase;
use \ParrotDb\Persistence\FeatherDatabase;

/**
 * Description of PSession
 *
 * @author J. Baum
 */
class PSession
{

    const DB_MEMORY = 1;
    const DB_XML = 2;
    const DB_FEATHER = 3;

    private $filePath;
    private $database;

    public function __construct($filePath, $dbEngine)
    {
        $this->filePath = $filePath;

        switch ($dbEngine) {
            case (self::DB_MEMORY):
                $this->database = new PMemoryDatabase();
                break;
            case (self::DB_XML):
                $this->database = new XmlDatabase($this->filePath);
                break;
            case (self::DB_FEATHER):
                $this->database = new FeatherDatabase($this->filePath);
                break;
            default:
                throw new PException(
                "The given database engine could not be found."
                );
        }
    }

    public function createPersistenceManager()
    {
        return new PPersistanceManager($this);
    }

    /**
     * Returns the current latest object ID and increases it by one.
     * 
     * @return \ParrotDb\ObjectModel\PObjectId
     */
    public function assignObjectId()
    {
        return $this->database->assignObjectId();
    }

    /**
     * Close the database session.
     */
    public function close()
    {
        PSessionFactory::closeSession($this->filePath);
    }

    /**
     * @return PDatabase
     */
    public function getDatabase()
    {
        return $this->database;
    }

}
