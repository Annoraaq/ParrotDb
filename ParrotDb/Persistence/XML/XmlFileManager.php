<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;

/**
 * Description of FileManager
 *
 * @author J. Baum
 */
class XmlFileManager {
    
    const DB_PATH = "pdb/";
    
    const DB_FILE_ENDING = ".pdb";
    
    protected $file;
    
    protected $xmlSerializer;
    
    protected $domDocument;
    
    protected $fileExists;
    
    protected $pObject;
    
    public function __construct() {
        $this->xmlSerializer = new XmlSerializer();
        $this->fileExists = false;
    }
    
    private function filePath() {
        return (self::DB_PATH
            . $this->pObject->getClass()->getName()
            . self::DB_FILE_ENDING
        );
    }
    
    private function openFile() {
        if (!file_exists(self::DB_PATH)) {
            mkdir(self::DB_PATH);
        }

        $this->file = fopen($this->filePath(), "w");
    }
    
    
    public function storeObject(PObject $pObject) {
        $this->pObject = $pObject;
        $this->loadXml();
        
        $this->openFile();
        $this->xmlSerializer->setDomDocument($this->domDocument);

        if ($this->fileExists) {
           $this->appendObject();
        } else {
            $this->insertFirstObject(); 
        }
        
        fwrite($this->file, $this->domDocument->saveXML());

        fclose($this->file);
    }
    
    private function insertFirstObject() {
        
        $class = $this->xmlSerializer->serializeClass(
            $this->pObject->getClass(),
            $this->domDocument
        );
        $objects = $this->domDocument->createElement("objects");
        
        $dbfile = $this->domDocument->createElement("dbfile");
        $dbfile->appendChild($class);
        $dbfile->appendChild($objects);
        $this->domDocument->appendChild($dbfile);
        
        $object = $this->xmlSerializer->serialize($this->pObject);
        $objects->appendChild($object);
    }

    private function appendObject() {
        $firstElem = $this->getFirstElementByName("objects");
        $firstElem->appendChild(
            $this->xmlSerializer->serialize($this->pObject)
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

    public function fetch(PObjectId $oid) {

//        if ($this->isFileExistent()) {
//            
//            if ($this->hasFileClass("Author")) {
//                $xml = simplexml_load_file(self::DB_PATH . "Author.pdb");
//                
//            }
//            $xml = simplexml_load_file($this->file);
//        }
//        
//        
//        
//        
//        
//        

    }
    
    private function loadXml() {
        $this->domDocument = new \DOMDocument();

        if ($this->isFileExistent($this->pObject->getClass()->getName())) {
            $this->fileExists = true;
            $this->domDocument->load($this->filePath());
        } else {
            $this->fileExists = false;
        }
    }

    private function isFileExistent($className) {
        return file_exists(self::DB_PATH . $className . ".pdb");
    }
    
//    private function hasFileClass($className) {
//        echo "\n\nfilenew: " . $this->isFileNew;
//        if ($this->isFileExistent() && !$this->isFileNew) {
//            $xml = simplexml_load_file(self::DB_PATH . $className . ".pdb");
//            
//            if (!isset($xml->class)) {
//                return false;
//            }
//            
//            return true;
//        }
//        
//        return false;
//    }
    
//    private function hasFileObjects($className) {
//        if ($this->isFileExistent()) {
//            $xml = simplexml_load_file(self::DB_PATH . $className . ".pdb");
//            
//            if (!isset($xml->objects)) {
//                return false;
//            }
//            
//            return true;
//        }
//        
//        return false;
//    }
    
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
                        throw new \ParrotDb\Core\PException("XML database file is corrupt: missing <id>-tag.");
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
