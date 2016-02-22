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
    
    private $bufferManager;
    
    private $limit;
    private $stored;
    
    /**
     * @param string $dbName
     */
    public function __construct($dbName) {
        $this->fileExists = false;
        $this->dbPath = static::DB_PATH . $dbName . '/';
        $this->dbName = $dbName;
        $this->objectSerializer = new FeatherObjectSerializer();
        $this->classSerializer = new FeatherClassSerializer();
        $this->bufferManager = new FeatherBufferManager();
        $this->limit = 10000000;
        $this->stored = 0;
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
            $this->fileExists = false;
            $this->file = fopen($this->toFilePath($fileName),"w+");
        }

        
        $this->fileName = $fileName;
    }
     
    /**
     * @param PObject $pObject PObject to store
     */
    public function storeObject(PObject $pObject) {
        
        $this->invalidateBuffer($pObject->getClass()->getName());
        $this->bufferManager->resetBuffer($this->toFilePath($pObject->getClass()->getName()));
        $this->pObject = $pObject;
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
        $this->featherStream = "";
        $this->stored = 0;

    }
    
    /**
     * @param array $arr PObject to store
     */
    public function storeObjects($arr) {
        
        foreach ($arr as $key => $val) {
            $this->bufferManager->resetBuffer($this->toFilePath($key));
            
            $this->openFile($key);
            $existed = $this->fileExists;
            fclose($this->file);
            foreach ($val as $temp) {
                $this->pObject = $temp;
                if ($existed) {
                    $this->delete($key, $temp->getObjectId());
                }
            }
            
            
            $this->openFile($key);
            foreach ($val as $temp) {
                $this->pObject = $temp;
                if ($existed) {
                    $this->appendObject();
                } else {
                    $this->insertFirstObject();
                    $existed = true;
                }
                
                if ($this->stored >= $this->limit) {
                    $res = fwrite($this->file, $this->featherStream);
                    $this->featherStream = "";
                    $this->stored = 0;
                }
            }
            
            

            $res = fwrite($this->file, $this->featherStream);
            fclose($this->file);
            $this->featherStream = "";
            $this->stored = 0;
        }
        
        

    }
    
   
    
    private function insertFirstObject() {
        $this->classSerializer->setPClass($this->pObject->getClass());
        $serClass = $this->classSerializer->serialize();
        $serClassLen = strlen($serClass);
        $this->featherStream = $serClass;
        $this->stored = $serClassLen;
        
        $this->objectSerializer->setPObject($this->pObject);
        $serObj =  $this->objectSerializer->serialize();
        $serObjLen = strlen($serObj);
        $this->featherStream .= $serObj;
        $this->stored += $serObjLen;

    }

    private function appendObject() {
        $this->objectSerializer->setPObject($this->pObject);
        //$this->removeOldObject();
        $serObj =  $this->objectSerializer->serialize();
        $serObjLen = strlen($serObj);
        $this->featherStream .= $serObj;
        $this->stored += $serObjLen;
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
    
    /**
     * 
     * @param PObjectId $oid
     * @return PObject
     * @throws PException
     */
    public function fetchConstraint(\ParrotDb\Query\Constraint\PConstraint $constraint) {
        
        $dbFiles = $this->fetchDbFiles();
        
        $objList = array();
        foreach ($dbFiles as $fileName) {
            
            $list = $this->fetchFromFileConstraint($fileName, $constraint);
            
            foreach ($list as $item) {
                $objList[$item->getObjectId()->getId()] = $item;
            }
        }
        
        return $objList;

    }
    
    private function fetchFromFileConstraint($className, \ParrotDb\Query\Constraint\PConstraint $constraint)
    {

       // if (!isset($this->buffer[$className])) {
            $featherParser = new FeatherParser($this->toFilePath($className));
            $featherParser->setBufferManager($this->bufferManager);
            return $featherParser->fetchConstraint($constraint);
        //}
        
        //return $this->buffer[$className];
    }
    
    
    
    private function fetchFromFile($className)
    {

        if (!isset($this->buffer[$className])) {
            $featherParser = new FeatherParser($this->toFilePath($className));
            $this->buffer[$className] = $featherParser->fetchAll();
        }
        
        return $this->buffer[$className];
    }
    
    private function invalidateBuffer($className) {
        unset($this->buffer[$className]);
    }

    private function fetchFrom($className, PObjectId $oid) {
        $featherParser = new FeatherParser($this->toFilePath($className));
        
        return $featherParser->fetch($oid);
    }
    
     public function delete($className, PObjectId $oid) {
         
         $this->invalidateBuffer($className);
         $this->bufferManager->resetBuffer($this->toFilePath($className));

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
