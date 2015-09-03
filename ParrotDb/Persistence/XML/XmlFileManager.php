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
    
    protected $file;
    
    protected $xmlSerializer;
    
    protected $isFileNew;
    
    public function __construct() {
        $this->xmlSerializer = new XmlSerializer();
    }
    
    private function openFile(PObject $pObject) {
        
        if (!file_exists(self::DB_PATH)) {
                mkdir(self::DB_PATH);
            }
        
        if (!file_exists(self::DB_PATH . $pObject->getClass()->getName() . ".pdb")) {
            $this->file = fopen(self::DB_PATH . $pObject->getClass()->getName() . ".pdb", "w");
            $this->isFileNew = true;
        } else {
            $this->file = fopen(self::DB_PATH . $pObject->getClass()->getName() . ".pdb", "w");
            $this->isFileNew = false;
        }
    }
    
    public function storeObject(PObject $pObject) {
                
        //$this->openFile($pObject);
        
        if ($this->hasFileClass($pObject->getClass()->getName())) {
            $xml = new \DOMDocument();
            $xml->load(self::DB_PATH . $pObject->getClass()->getName() . ".pdb");
            
            if ($this->hasFileObjects($pObject->getClass()->getName())) {
                //$domDoc = new \DOMDocument();
               // $domDoc->appendChild(dom_import_simplexml($xml)->ownerDocument);
 

                $this->xmlSerializer->setDomDocument($xml);
                $firstElem = null;
                foreach ($xml->getElementsByTagName("objects") as $objects) {
                    $firstElem = $objects;
                    break;
                }
                
                $firstElem->appendChild($this->xmlSerializer->serialize($pObject));
                $this->openFile($pObject);
                fwrite($this->file, $xml->saveXML());
        
                fclose($this->file);
                
                //$xml->objects->addChild(simplexml_import_dom($this->xmlSerializer->serialize($pObject)));
            } else {
                
            }
        } else {
            $this->openFile($pObject);
            $xml = new \DOMDocument();
            $dbfile = $xml->createElement("dbfile");
            $xml->appendChild($dbfile);
            $this->xmlSerializer->setDomDocument($xml);
            $class = $this->xmlSerializer->serializeClass($pObject->getClass(), $xml);
            $dbfile->appendChild($class);
            $objects = $xml->createElement("objects");
            $dbfile->appendChild($objects);
            $object = $this->xmlSerializer->serialize($pObject);
            $objects->appendChild($object);
            fwrite($this->file, $xml->saveXML());
        
            fclose($this->file);
        }
         
        //if ($this->isFileNew) {
//            $xml = new \DomDocument();
//            $dbfile = $xml->createElement("dbfile");
//            $xml->appendChild($dbfile);
//            $this->xmlSerializer->setDomDocument($xml);
//            $class = $this->xmlSerializer->serializeClass($pObject->getClass(), $xml);
//            $dbfile->appendChild($class);
//            $objects = $xml->createElement("objects");
//            $dbfile->appendChild($objects);
//            $object = $this->xmlSerializer->serialize($pObject);
//            $objects->appendChild($object);
        //}
        //$xml = $this->xmlSerializer->serialize($pObject);
//        fwrite($this->file, $xml->saveXML());
//        
//        fclose($this->file);
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
    
    private function isFileExistent() {
        // :todo replace fixed string "Author"
        return file_exists(self::DB_PATH . "Author.pdb");
    }
    
    private function hasFileClass($className) {
        if ($this->isFileExistent()) {
            $xml = simplexml_load_file(self::DB_PATH . $className . ".pdb");
            
            if (!isset($xml->class)) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    private function hasFileObjects($className) {
        if ($this->isFileExistent()) {
            $xml = simplexml_load_file(self::DB_PATH . $className . ".pdb");
            
            if (!isset($xml->objects)) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    public function isObjectStored(PObjectId $oid) {
        if ($this->isFileExistent()) {
            $xml = simplexml_load_file(self::DB_PATH . "Author.pdb");
            
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
