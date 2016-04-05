<?php

namespace ParrotDb\Persistence\Feather;

use ParrotDb\ObjectModel\PObject;
use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;
use ParrotDb\Query\Constraint\PConstraint;

/**
 * Description of FileManager
 *
 * @author J. Baum
 */
class FeatherFileManager
{

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
    private $memoryLimit;
    private $charsStored;
    private $existed;

    /**
     * @param string $dbName
     */
    public function __construct($dbName, $memoryLimit = 10000000)
    {
        $this->fileExists = false;
        $this->dbPath = static::DB_PATH . $dbName . '/';
        $this->dbName = $dbName;
        $this->objectSerializer = new FeatherObjectSerializer();
        $this->classSerializer = new FeatherClassSerializer();
        $this->bufferManager = new FeatherBufferManager();
        $this->memoryLimit = $memoryLimit;
        $this->charsStored = 0;
        $this->existed = false;
    }

    private function toFilePath($className)
    {
        $cleanClassName = str_replace('\\', '-', $className);
        return ($this->dbPath
         . $cleanClassName
         . self::DB_FILE_ENDING
         );
    }

    private function openFile($fileName)
    {
        if (!file_exists($this->dbPath)) {
            mkdir($this->dbPath);
        }

        if (file_exists($this->toFilePath($fileName))) {
            $this->fileExists = true;
            $this->file = fopen($this->toFilePath($fileName), "a+");
        } else {
            $this->fileExists = false;
            $this->file = fopen($this->toFilePath($fileName), "w+");
        }

        $this->fileName = $fileName;
    }

    private function closeFile()
    {
        fclose($this->file);
    }

    private function writeFeatherStream()
    {
        fwrite($this->file, $this->featherStream);
        $this->featherStream = "";
        $this->charsStored = 0;
    }

    /**
     * @param PObject $pObject PObject to store
     */
    public function storeObject(PObject $pObject)
    {
        $this->bufferManager->resetBuffer(
         $this->toFilePath($pObject->getClass()->getName())
        );
        $this->pObject = $pObject;
        $this->openFile($this->pObject->getClass()->getName());

        if ($this->fileExists) {

            $this->closeFile();
            $this->delete(
             $pObject->getClass()->getName(), $pObject->getObjectId()
            );
            $this->openFile($this->pObject->getClass()->getName());

            $this->appendObject();
        } else {
            $this->insertFirstObject();
        }

        $this->writeFeatherStream();
        $this->closeFile();
    }

    private function deleteOldObjects($objects, $className)
    {
        foreach ($objects as $pObj) {
            $this->pObject = $pObj;
            if ($this->existed) {
                $this->delete($className, $pObj->getObjectId());
            }
        }
    }

    private function insertObjects($className, $objects)
    {
        $this->openFile($className);
        foreach ($objects as $temp) {
            $this->pObject = $temp;
            if ($this->existed) {
                $this->appendObject();
            } else {
                $this->insertFirstObject();
                $this->existed = true;
            }

            if ($this->isMemoryLimitReached()) {
                $this->writeFeatherStream();
            }
        }

        $this->writeFeatherStream();
        $this->closeFile();
    }

    /**
     * @param array $arr PObject to store
     */
    public function storeObjects($arr)
    {
        foreach ($arr as $fileName => $objects) {
            $this->bufferManager->resetBuffer($this->toFilePath($fileName));
            $this->checkExistence($fileName);
            $this->deleteOldObjects($objects, $fileName);
            $this->insertObjects($fileName, $objects);
        }
    }

    private function checkExistence($fileName)
    {
        $this->openFile($fileName);
        $this->existed = $this->fileExists;
        $this->closeFile();
    }

    private function isMemoryLimitReached()
    {
        return ($this->charsStored >= $this->memoryLimit);
    }

    private function insertFirstObject()
    {
        $this->classSerializer->setPClass($this->pObject->getClass());
        $serClass = $this->classSerializer->serialize();
        $serClassLen = mb_strlen($serClass);
        $this->featherStream = $serClass;
        $this->charsStored = $serClassLen;

        $this->objectSerializer->setPObject($this->pObject);
        $serObj = $this->objectSerializer->serialize();
        $serObjLen = mb_strlen($serObj);
        $this->featherStream .= $serObj;
        $this->charsStored += $serObjLen;
    }

    private function appendObject()
    {
        $this->objectSerializer->setPObject($this->pObject);
        $serObj = $this->objectSerializer->serialize();
        $serObjLen = mb_strlen($serObj);
        $this->featherStream .= $serObj;
        $this->charsStored += $serObjLen;
    }

    /**
     * 
     * @param PObjectId $oid
     * @return PObject
     * @throws PException
     */
    public function fetch(PObjectId $oid)
    {
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
     * @param \ParrotDb\Query\Constraint\PConstraint $constraint
     * @return array
     */
    public function fetchConstraint(PConstraint $constraint)
    {
        $dbFiles = $this->fetchDbFiles();

        $objList = array();
        foreach ($dbFiles as $fileName) {

            $list = $this->fetchFromFileConstraint($fileName, $constraint);

            foreach ($list as $item) {
                $objList[$item->getObjectId()->getId()] = $item;
            }
        }

        $resultSet = new \ParrotDb\Query\PResultSet();
        foreach ($objList as $key => $val) {
            $resultSet->add($val);
        }
        return $resultSet;
    }

    private function fetchFromFileConstraint($className, PConstraint $constraint)
    {
        $featherParser = new FeatherParser($this->toFilePath($className));
        $featherParser->setBufferManager($this->bufferManager);
        return $featherParser->fetchConstraint($constraint);
    }

    private function fetchFrom($className, PObjectId $oid)
    {
        $featherParser = new FeatherParser($this->toFilePath($className));
        return $featherParser->fetch($oid);
    }

    /**
     * @param string $className
     * @param PObjectId $oid
     */
    public function delete($className, PObjectId $oid)
    {
        //$this->bufferManager->resetBuffer($this->toFilePath($className));
        $this->bufferManager->removeFromBuffer($this->toFilePath($className), $oid->getId());

        $featherParser = new FeatherParser($this->toFilePath($className));
        $featherParser->setInvalid($oid);
    }
    
    /**
     * @param string $className
     * @param array $oIds
     */
    public function deleteArray($objects)
    {
        $oids = array();
        $className = false;
        foreach ($objects as $obj) {
            $className = $obj->getClass()->getName();
            $this->bufferManager->removeFromBuffer(
                $this->toFilePath($obj->getClass()->getName()),
                $obj->getObjectId()->getId()
            );
            $oids[$obj->getObjectId()->getId()] = $obj->getObjectId()->getId();
        }
        
        if ($className != false) {
            $featherParser = new FeatherParser($this->toFilePath($className));
            $featherParser->setInvalidArray($oids);
        }
        
        
    }

    private function deserialize(\DomElement $object)
    {
        $classDeserializer = new FeatherClassDeserializer($this->domDocument);
        $pClass = $classDeserializer->deserialize();

        $objectDeserializer = new FeatherObjectDeserializer($object, $pClass);
        return $objectDeserializer->deserialize();
    }

    private function fetchDbFiles()
    {
        $scanDir = scandir($this->dbPath);

        $filtered = [];
        foreach ($scanDir as $entry) {
            if (\ParrotDb\Utils\PUtils::endsWith($entry,
              $this->dbName . static::DB_FILE_ENDING)) {
                continue;
            }
            if ($this->getFileEnding($entry) == self::DB_FILE_ENDING) {
                $filtered[] = $this->removeFileEnding($entry);
            }
        }

        return $filtered;
    }

    private function removeFileEnding($filename)
    {
        return substr(
         $filename, 0, strlen($filename) - strlen(self::DB_FILE_ENDING)
        );
    }

    private function getFileEnding($filename)
    {
        return substr(
         $filename, strlen($filename) - strlen(self::DB_FILE_ENDING)
        );
    }

    /**
     * @param PObjectId $oid
     * @return boolean
     */
    public function isObjectStored(PObjectId $oid)
    {
        $dbFiles = $this->fetchDbFiles();
        foreach ($dbFiles as $dbFile) {
            if ($this->isObjectStoredIn($oid, $dbFile)) {
                return true;
            }
        }

        return false;
    }

    private function isObjectStoredIn($oid, $className)
    {
        $featherParser = new FeatherParser($this->toFilePath($className));
        return $featherParser->isObjectStoredIn($oid);
    }

}
