<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;

/**
 * Description of FileManager
 *
 * @author J. Baum
 */
class XmlFileManager {
    
    const DB_PATH = "pdb/";
    
    const DB_FILE_ENDING = ".pdb";
    
    protected $file;
    
    protected $objectSerializer;
    
    protected $classSerializer;
    
    protected $domDocument;
    
    protected $fileExists;
    
    protected $pObject;
    
    protected $fileName;
    
    /**
     * 
     */
    public function __construct() {
        $this->resetDomDocument();
        $this->fileExists = false;
    }
    
    private function resetDomDocument() {
        $domDocument = new \DOMDocument;
        $this->objectSerializer = new XmlObjectSerializer($domDocument);
        $this->classSerializer = new XmlClassSerializer($domDocument);
    }
    
    private function filePath() {
        return $this->toFilePath(
                $this->pObject->getClass()->getName()
            );
    }
    
    private function toFilePath($className) {
        return (self::DB_PATH
            . $className
            . self::DB_FILE_ENDING
        );
    }
    
    private function openFile($fileName) {
        if (!file_exists(self::DB_PATH)) {
            mkdir(self::DB_PATH);
        }

        $this->file = fopen($this->toFilePath($fileName),"w");
        $this->fileName = $fileName;
    }
     
    /**
     * @param PObject $pObject PObject to store
     */
    public function storeObject(PObject $pObject) {
        $this->pObject = $pObject;
        $this->loadXml($this->pObject->getClass()->getName());
        
        $this->openFile($this->pObject->getClass()->getName());
        $this->objectSerializer->setDomDocument($this->domDocument);
        $this->classSerializer->setDomDocument($this->domDocument);

        if ($this->fileExists) {
           $this->appendObject();
        } else {
            $this->insertFirstObject(); 
        }
        
        fwrite($this->file, $this->domDocument->saveXML());
        
        fclose($this->file);
    }
    
    private function insertFirstObject() {
        $this->classSerializer->setPClass($this->pObject->getClass());
        $class = $this->classSerializer->serialize();
        $objects = $this->domDocument->createElement("objects");
        
        $dbfile = $this->domDocument->createElement("dbfile");
        $dbfile->appendChild($class);
        $dbfile->appendChild($objects);
        $this->domDocument->appendChild($dbfile);
        
        $this->objectSerializer->setPObject($this->pObject);
        $object = $this->objectSerializer->serialize();
        $objects->appendChild($object);
    }

    private function appendObject() {
        $firstElem = $this->getFirstElementByName("objects");
        $this->objectSerializer->setPObject($this->pObject);
        $firstElem->appendChild(
            $this->objectSerializer->serialize()
        );
    }
    
    private function getFirstElementByName($name) {
        $firstElem = null;
        foreach ($this->domDocument->getElementsByTagName($name) as $objects) {
            $firstElem = $objects;
            break;
        }
        
        return $firstElem;
    }
    
    private function getFirstElementByName2($dom, $name) {
        foreach ($dom->getElementsByTagName($name) as $objects) {
            return $objects;
        }
        
        return null;
    }

    /**
     * 
     * @param PObjectId $oid
     * @return PObject
     * @throws PException
     */
    public function fetch(PObjectId $oid) {
        
        $this->domDocument = new \DOMDocument();
        $dbFiles = $this->fetchDbFiles();
        
        foreach ($dbFiles as $fileName) {
            
            $obj = $this->fetchFrom($fileName, $oid);
            if ($obj !== null) {
                return $obj;
            }
        }
        
        throw new PException(
            "Object with id "
            . $oid->getId() 
            . " not persisted."
        );

    }
    
    private function fetchFrom($className, PObjectId $oid) {
        $this->loadXml($className);

        $objects = $this->domDocument->getElementsByTagName("object");
        
        $found = false;
        $foundObject = null;
        foreach ($objects as $object) {
            if ($oid->getId() == $this->getFirstElementByName2($object, "id")->nodeValue) {
                $found = true;
                $foundObject = $this->deserialize($object);
                break;
            }
        }

        return $foundObject;
        
    }
    
    private function deserialize(\DomElement $object) {
        
        $classDeserializer = new XmlClassDeserializer($this->domDocument);
        $pClass = $classDeserializer->deserialize();
  
        $objectDeserializer = new XmlObjectDeserializer($object, $pClass);
        return $objectDeserializer->deserialize();
        
    }
    
   
    
    private function loadXml($className) {
        $this->domDocument = new \DOMDocument();

        if ($this->isFileExistent($className)) {
            $this->fileExists = true;
            $this->domDocument->load($this->toFilePath($className));
        } else {
            $this->fileExists = false;
        }
    }

    private function isFileExistent($className) {
        return file_exists(self::DB_PATH . $className . ".pdb");
    }
    
    
    private function fetchDbFiles() {
        $scanDir = scandir(self::DB_PATH);
        
        $filtered = [];
        foreach ($scanDir as $entry) {
            if ($this->getFileEnding($entry) == self::DB_FILE_ENDING) {
                $filtered[] = $this->removeFileEnding($entry);
            }
        }
        
        return $filtered;
    }
    
    private function removeFileEnding($filename) {
        return substr(
            $filename,
            0,
            strlen($filename)-strlen(self::DB_FILE_ENDING)
        );
    }
    
    private function getFileEnding($filename) {
        return substr(
            $filename,
            strlen($filename)-strlen(self::DB_FILE_ENDING)
        );
    }
    
    public function isObjectStored(PObjectId $oid) {
        $dbFiles = $this->fetchDbFiles();
        foreach ($dbFiles as $dbFile) {
            if ($this->isObjectStoredIn($oid, $dbFile)) {
                return true;
            }
        }

        return false;    
    }
    
    private function isObjectStoredIn($oid, $className) {
        if ($this->isFileExistent($className)) {
            $xml = simplexml_load_file(self::DB_PATH . $className . ".pdb");
            
            if (!isset($xml->objects)) {
                return false;
            } else {
                foreach ($xml->objects->children() as $object) {
                    if (!isset($object->id)) {
                        throw new \ParrotDb\Core\PException(
                            "XML database file is corrupt: missing <id>-tag."
                        );
                    }
                    if (intval($object->id) == $oid->getId()) {
                        return true;
                    }
                }
            }
        }
        
 
        return false;
    }
    
            

}
