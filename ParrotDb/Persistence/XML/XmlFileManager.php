<?php

namespace ParrotDb\Persistence\XML;

use ParrotDb\ObjectModel\PObject;
use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;
use ParrotDb\Utils\PXmlUtils;

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
    
    private $dbPath;
    private $dbName;
    
    /**
     * @param string $dbName
     */
    public function __construct($dbName) {
        $this->resetDomDocument();
        $this->fileExists = false;
        $this->dbPath = static::DB_PATH . $dbName . '/';
        $this->dbName = $dbName;
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
        $className = str_replace('\\', '-', $className);
        return ($this->dbPath
            . $className
            . self::DB_FILE_ENDING
        );
    }
    
    private function openFile($fileName) {
        if (!file_exists($this->dbPath)) {
            mkdir($this->dbPath);
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
        $this->removeOldObject();
        $firstElem->appendChild(
            $this->objectSerializer->serialize()
        );
    }
    
    private function removeOldObject() {
        $objects = $this->domDocument->getElementsByTagName("object");
        
        foreach ($objects as $object) {
            if (PXmlUtils::firstElemByTagName($object, "id")->nodeValue == $this->pObject->getObjectId()->getId()) {
                PXmlUtils::firstElemByTagName($this->domDocument->firstChild, "objects")->removeChild($object);
            }
        }
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
    
    /**
     * 
     * @param PObjectId $oid
     * @return PObject
     * @throws PException
     */
    public function fetchAll() {
        
        $this->domDocument = new \DOMDocument();
        $dbFiles = $this->fetchDbFiles();
        
        $objList = array();
        foreach ($dbFiles as $fileName) {
            
            $list = $this->fetchFromFile($fileName);
            
            foreach ($list as $item) {
                $objList[$item->getObjectId()->getId()] = $item;
            }
        }
        
        return $objList;

    }
    
    private function fetchFromFile($className) {
        $this->loadXml($className);
        $objectList = array();
        $objects = $this->domDocument->getElementsByTagName("object");

        foreach ($objects as $object) {
            $objectList[] = $this->deserialize($object);
        }

        return $objectList;
    }

    private function fetchFrom($className, PObjectId $oid) {
        $this->loadXml($className);

        $objects = $this->domDocument->getElementsByTagName("object");
        
        $foundObject = null;
        foreach ($objects as $object) {
            if ($oid->getId() == $this->getFirstElementByName2($object, "id")->nodeValue) {
                $foundObject = $this->deserialize($object);
                break;
            }
        }

        return $foundObject;
        
    }
    
     public function delete($className, PObjectId $oid) {

        $this->loadXml($className);

        $objectsNode = $this->getFirstElementByName("objects");
        $objects = $this->domDocument->getElementsByTagName("object");

        foreach ($objects as $object) {
            if ($oid->getId() == $this->getFirstElementByName2($object, "id")->nodeValue) {
                $objectsNode->removeChild($object);
                break;
            }
        }

        $this->openFile($className);
        
        fwrite($this->file, $this->domDocument->saveXML());

        
        fclose($this->file);
        
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
        return file_exists($this->toFilePath($className));
    }
    
    
    private function fetchDbFiles() {
        $scanDir = scandir($this->dbPath);
        
        $filtered = [];
        foreach ($scanDir as $entry) {
            if (\ParrotDb\Utils\PUtils::endsWith($entry, $this->dbName . static::DB_FILE_ENDING)) {
                continue;
            }
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
            $xml = simplexml_load_file($this->dbPath . $className . ".pdb");
            
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
