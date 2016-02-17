<?php

namespace ParrotDb\Persistence\Feather;

use ParrotDb\ObjectModel\PObject;
use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;
use ParrotDb\Utils\PXmlUtils;

/**
 * Description of FileManager
 *
 * @author J. Baum
 */
class FeatherFileManager {
    
    const DB_PATH = "pdb/";
    
    const DB_FILE_ENDING = ".pdb";
    
    protected $file;
    
    protected $objectSerializer;
    
    protected $classSerializer;
    
    protected $fileExists;
    
    protected $pObject;
    
    protected $fileName;
    
    private $dbPath;
    private $dbName;
    
    private $featherStream;
    
    /**
     * @param string $dbName
     */
    public function __construct($dbName) {
        $this->fileExists = false;
        $this->dbPath = static::DB_PATH . $dbName . '/';
        $this->dbName = $dbName;
        $this->objectSerializer = new FeatherObjectSerializer();
        $this->classSerializer = new FeatherClassSerializer();
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
        
        if (file_exists($this->toFilePath($fileName))) {
            $this->fileExists = true;
            $this->file = fopen($this->toFilePath($fileName),"a+");
        } else {
            $this->file = fopen($this->toFilePath($fileName),"w+");
        }

        
        $this->fileName = $fileName;
    }
     
    /**
     * @param PObject $pObject PObject to store
     */
    public function storeObject(PObject $pObject) {
        $this->pObject = $pObject;
        echo "\nstore " . $this->pObject->getObjectId()->getId() . "\n" ;

        $this->openFile($this->pObject->getClass()->getName());
        
        if ($this->fileExists) {
            
            fclose($this->file);
            $this->delete($pObject->getClass()->getName(), $pObject->getObjectId());
$this->openFile($this->pObject->getClass()->getName());
           // $this->removeOldObject();

          // $this->removeOldObject();
           //$this->openFile($this->pObject->getClass()->getName());
           //$this->openFile($this->pObject->getClass()->getName());
           $this->appendObject();
        } else {
           $this->insertFirstObject(); 
        }
        
        //$this->openFile($this->pObject->getClass()->getName());
        
        $res = fwrite($this->file, $this->featherStream);

        fclose($this->file);
        

    }
    
    private function insertFirstObject() {
        echo "insert";
        $this->classSerializer->setPClass($this->pObject->getClass());
        $this->featherStream = $this->classSerializer->serialize();
        
        $this->objectSerializer->setPObject($this->pObject);
        $this->featherStream .= $this->objectSerializer->serialize();
        
        echo "\n" . $this->featherStream . "\n";
    }

    private function appendObject() {
        echo "append " . $this->pObject->getObjectId()->getId() . "\n" ;
        $this->objectSerializer->setPObject($this->pObject);
        //$this->removeOldObject();
        $this->featherStream = $this->objectSerializer->serialize();
        echo $this->featherStream . "\n";
        //fseek($this->file, 0, SEEK_END);
    }
    
    private function removeOldObject() {
        $featherParser = new FeatherParser($this->toFilePath($this->pObject->getClass()->getName()));
//
        $featherParser->setInvalid($this->pObject->getObjectId());
        
        
    }
    
    /**
     * 
     * @param PObjectId $oid
     * @return PObject
     * @throws PException
     */
    public function fetch(PObjectId $oid) {
        $dbFiles = $this->fetchDbFiles();
        
        foreach ($dbFiles as $fileName) {
            $obj = $this->fetchFrom($fileName, $oid);
            if ($obj !== false) {
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
        $featherParser = new FeatherParser($this->toFilePath($className));
        return $featherParser->fetchAll();
    }

    private function fetchFrom($className, PObjectId $oid) {
        $featherParser = new FeatherParser($this->toFilePath($className));
        
        return $featherParser->fetch($oid);
    }
    
     public function delete($className, PObjectId $oid) {

         echo "\ndelete oid " . $oid->getId() . "\n";
        $featherParser = new FeatherParser($this->toFilePath($className));
        $featherParser->setInvalid($oid);
        

//        $this->loadXml($className);

//        $objectsNode = $this->getFirstElementByName("objects");
//        $objects = $this->domDocument->getElementsByTagName("object");

//        foreach ($objects as $object) {
//            if ($oid->getId() == $this->getFirstElementByName2($object, "id")->nodeValue) {
//                $objectsNode->removeChild($object);
//                break;
//            }
//        }
//
//        $this->openFile($className);
//        
//        fwrite($this->file, $this->domDocument->saveXML());
//
//        
//        fclose($this->file);
        
    }
    
    private function deserialize(\DomElement $object) {
        
        $classDeserializer = new FeatherClassDeserializer($this->domDocument);
        $pClass = $classDeserializer->deserialize();
  
        $objectDeserializer = new FeatherObjectDeserializer($object, $pClass);
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
        $featherParser = new FeatherParser($this->toFilePath($className));
        return $featherParser->isObjectStoredIn($oid);
    }
    
            

}
