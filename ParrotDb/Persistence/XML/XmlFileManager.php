<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\ObjectModel\PObject;

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
        if (!file_exists(self::DB_PATH . $pObject->getClass()->getName() . ".pdb")) {
            mkdir(self::DB_PATH);
            $this->file = fopen(self::DB_PATH . $pObject->getClass()->getName() . ".pdb", "w");
            $this->isFileNew = true;
        } else {
            $this->file = fopen(self::DB_PATH . $pObject->getClass()->getName() . ".pdb", "w");
            $this->isFileNew = false;
        }
    }
    
    public function storeObject(PObject $pObject) {
                
        $this->openFile($pObject);
         
        //if ($this->isFileNew) {
            $xml = new \DomDocument();
            $xml = $this->xmlSerializer->serializeClass($pObject->getClass(), $xml);
            $objects = $xml->createElement("objects");
            $xml->appendChild($objects);
            $xml = $this->xmlSerializer->serialize($pObject,$xml, $objects);
        //}
        //$xml = $this->xmlSerializer->serialize($pObject);
        fwrite($this->file, $xml->saveXML());
        
        fclose($this->file);
    }
    
            

}
