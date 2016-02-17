<?php

namespace ParrotDb\Persistence\Feather;

use ParrotDb\Utils\VirtualString;
use ParrotDb\Utils\VirtualWriteString;
use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;

/**
 * The Featherparser handles the parsing of a feather file
 *
 * @author J. Baum
 */
class FeatherParser {
    
    private $virtualString;
    private $fileName;
    private $chunkSize;
    
    public function __construct($fileName, $chunkSize = 10024) {
        $this->fileName = $fileName;
        $this->chunkSize = $chunkSize;
    }
    
    /**
     * @param int $position
     */
    private function notFound($position) {
        return ($position < 0);
    }
    
    private function getEndOfClassSection() {

        $endOfClass = $this->virtualString->findFirst(']');
        if ($this->notFound($endOfClass)) {
            
            $this->virtualString->close();
            throw new PException(
                "Malformed feather database file: " . $this->fileName . ": " . $this->virtualString->getWindow()
            );
        }
        
        return $endOfClass;
    }
    
    private function isEndOfFile($position) {
       return $this->notFound($this->virtualString->findFirst("[", $position));
    }
    
    private function isInvalid($objectId) {
        return (isset($objectId[0]) && $objectId[0] == "i");
    }
    
     
    
    /**
     * @param PObjectId $objectId
     */
    public function isObjectStoredIn(PObjectId $objectId) {
        $this->virtualString = new VirtualString($this->fileName, $this->chunkSize);
        
        $this->virtualString->open();
        
        $objectStartPos = $this->getEndOfClassSection();

        $found = false;
        while (true) {
            
            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }
            $nextObjectId = $this->getNextObjectId($objectStartPos);
            if (!$this->isInvalid($nextObjectId) && $nextObjectId == $objectId->getId()) {
                
                $found = true;
                break;
            } else {
                $objectStartPos = $this->getNextObjectPosition($objectStartPos);
            }
        }
        
       
        
        $this->virtualString->close();  

        return $found;
    }
    
    private function getNextObjectId($objectStartPos) {
        $oid = $this->virtualString->getNextInterval(
          $objectStartPos, "[", ","
        );
        
        return $oid;
    }
    
    private function getNextObject($objectStartPos) {
        return $this->virtualString->getNextInterval(
          $objectStartPos, "[", "]"
        );
    }
    
    private function getNextObjectPosition($objectStartPos)
    {
        
        
        // :todo
        // IMPORTANT!
        // consider object id length!!! currently a constant factor of 2 is used
        // which is not suficcient!
        
        $lengthStart = $this->virtualString->findFirst(",", $objectStartPos);
        $len = $this->virtualString->getNextInterval($lengthStart,",",",");
        $lenOfLen = strlen($len)+2;
//        echo "\nObjectStartPos = $lengthStart, length = $len, lenOfLen = $lenOfLen\n"; 
//        echo $this->virtualString->substr($lengthStart
//            + $len
//            + $lenOfLen, $lengthStart
//            + $len
//            + $lenOfLen+1) . "\n";

        return
            $lengthStart
            + $len
            + $lenOfLen;

    }
    
    /**
     * @param PObjectId $objectId
     */
    public function fetch(PObjectId $objectId) {
        
        $this->virtualString = new VirtualString($this->fileName, $this->chunkSize);
        
        $this->virtualString->open();
        
        $objectStartPos = $this->getEndOfClassSection();
        
        $object = false;
        while (true) {
            
            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }
            $nextObjectId = $this->getNextObjectId($objectStartPos);
            if (!$this->isInvalid($nextObjectId) && $nextObjectId == $objectId->getId()) {
                $object = "[" . $this->getNextObject($objectStartPos) . "]";
                break;
            } else {
                
                $objectStartPos = $this->getNextObjectPosition($objectStartPos);
            }
        }
        
        $this->virtualString->close();  

        
        if ($object != false) {
            $objectDeserializer = new FeatherObjectDeserializer($this->getClass());
            $objectDeserializer->setInput($object);
           return $objectDeserializer->deserialize();
        }
        return false;
    }
    
    /**
     * @return array All deserialized PObjects
     */
    public function fetchAll() {
        
        
        $this->virtualString = new VirtualString($this->fileName, $this->chunkSize);
        
        
        $class = $this->getClass();
        $objectDeserializer = new FeatherObjectDeserializer($class);
        $this->virtualString->open();
        
        $objectStartPos = $this->getEndOfClassSection();

        $objects = array();

        while (true) {

            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }
            
            $obj = "["
                . $this->getNextObject($objectStartPos)
                . "]";
            
            $oid = $this->getNextObjectId($objectStartPos);
            if ((!isset($oid[0]) || $oid[0] != 'i')) {
                $objectDeserializer->setInput(
                      $obj
                );
                $temp = $objectDeserializer->deserialize();
                $objects[] = $temp;
            }

            
            $objectStartPos = $this->getNextObjectPosition($objectStartPos);

 
        }


        
        $this->virtualString->close();  
        
        return $objects;
    }
    

    public function parse() {
        
    }
    
    public function getClass() {
        $this->virtualString = new VirtualString($this->fileName, $this->chunkSize);
        $classDeserializer = new FeatherClassDeserializer();
        
        $this->virtualString->open();
        $input = "c" . $this->virtualString->getNextInterval(0,"c[","]") . "]";
        $classDeserializer->setInput($input);
        $class = $classDeserializer->deserialize();
        $this->virtualString->close();
        
        return $class;
    }
    
    public function setInvalidOLD(PObjectId $objectId) {
        $this->virtualString = new VirtualWriteString($this->fileName, $this->chunkSize);
        
        $this->virtualString->open();
        
        $objectStartPos = $this->getEndOfClassSection();

        while (true) {
            
            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }
            
            if ($this->getNextObjectId($objectStartPos) == $objectId->getId()) {
                break;
            } else {
                $objectStartPos = $this->getNextObjectPosition($objectStartPos);
            }
        }
        
        echo "\nWIN: " . $this->virtualString->getWindow() . "\n";
        
        $this->virtualString->close();  

    }
    
    public function setInvalid(PObjectId $objectId) {
        $this->virtualString = new VirtualWriteString($this->fileName, $this->chunkSize);
        
        $this->virtualString->open();
        
        $objectStartPos = $this->getEndOfClassSection();
                    
        echo $this->virtualString->getWindow() . "\n";
        echo $this->virtualString->substr($objectStartPos, $objectStartPos+10) . "\n";
        //$objectStartPos = $this->getNextObjectPosition($objectStartPos);

        
        echo "\nobject Start: " . $objectStartPos . "\n";
        
        
        $object = false;
        while (true) {
            

            
            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }

            
            $nextObjectId = $this->getNextObjectId($objectStartPos);
            if (!$this->isInvalid($nextObjectId) && $nextObjectId == $objectId->getId()) {
                echo "\nreplace $objectStartPos\n";
                $this->virtualString->replace($objectStartPos+2,"j");
                break;
            } else {
                $objectStartPos = $this->getNextObjectPosition($objectStartPos);
            }
        }
        
        $this->virtualString->close();  

        return false;
    }

    
    
}
