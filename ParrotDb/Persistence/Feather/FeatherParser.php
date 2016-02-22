<?php

namespace ParrotDb\Persistence\Feather;

use ParrotDb\Utils\VirtualString;
use ParrotDb\Utils\VirtualWriteString;
use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;
use ParrotDb\Query\Constraint\PConstraint;

/**
 * The Featherparser handles the parsing of a feather file
 *
 * @author J. Baum
 */
class FeatherParser {
    
    private $virtualString;
    private $fileName;
    private $chunkSize;
    private $bufferManager;
    
    public function __construct($fileName, $chunkSize = 10024) {
        $this->fileName = $fileName;
        $this->chunkSize = $chunkSize;
    }
    
    public function setBufferManager(FeatherBufferManager $bufferManager) {
        $this->bufferManager = $bufferManager;
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
    public function fetch(PObjectId $objectId, $index = array()) {
        
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
    
    /**
     * @return array All deserialized PObjects
     */
    public function fetchConstraint(PConstraint $constraint) {
        
        $constraintProcessor = new \ParrotDb\Query\Constraint\PXmlConstraintProcessor();
        $this->virtualString = new VirtualString($this->fileName, $this->chunkSize);
        $res = array();

        $buuf = new \ParrotDb\Query\PResultSet();
        if ($this->bufferManager->isWholeFileInBuffer($this->fileName)) {
            $constraintProcessor->setPersistedObjects($this->bufferManager->getBuffer($this->fileName));
            
            $buuf =  $constraintProcessor->process($constraint);
            return $buuf;
        }
        
        

        $class = $this->getClass();
        $objectDeserializer = new FeatherObjectDeserializer($class);
        $this->virtualString->open();
                
        $objects = array();
        
        if($this->bufferManager->getBufferOffset($this->fileName) > 0) {
            foreach ($this->bufferManager->getBuffer($this->fileName) as $temp) {
                $objects[] = $temp;
            }
            $objectStartPos = $this->bufferManager->getBufferOffset($this->fileName);
        } else {
            $objectStartPos = $this->getEndOfClassSection();
            $this->bufferManager->setBufferOffset($this->fileName, $objectStartPos);
        }
        
        
        $charactersRead = 0;
        $limit = 10000000;
       // $limit = 500;


        while (true) {

            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }
            
            $obj = "["
                . $this->getNextObject($objectStartPos)
                . "]";
            
            $objLen = strlen($obj);
            $charactersRead += $objLen;
            
            if ($charactersRead > $limit) {
                $constraintProcessor->setPersistedObjects($objects);
                $tempRes = $constraintProcessor->process($constraint);
                foreach ($tempRes as $tmp) {
                    $res[] = $tmp;
                }
                
    
                $charactersRead = 0;
                $objects = array();
            }

            $oid = $this->getNextObjectId($objectStartPos);
            if ((!isset($oid[0]) || $oid[0] != 'i')) {
                $objectDeserializer->setInput(
                      $obj
                );
                $temp = $objectDeserializer->deserialize();
                
                if (($this->bufferManager->getBufferOffset($this->fileName) + $objLen) <= $limit) {
                    $this->bufferManager->setWholeFileInBuffer($this->fileName, true);
                    $this->bufferManager->setBufferOffset(
                        $this->fileName,
                        $this->bufferManager->getBufferOffset($this->fileName)+$objLen
                    );
                    $this->bufferManager->addToBuffer($this->fileName, $temp);
                } else {
                    $this->bufferManager->setWholeFileInBuffer($this->fileName, false);
                }
                $objects[] = $temp;
            }

            
            $objectStartPos = $this->getNextObjectPosition($objectStartPos);

 
        }


        
        $this->virtualString->close();  
        
        
        $constraintProcessor->setPersistedObjects($objects);
        $tempRes = $constraintProcessor->process($constraint);
        foreach ($tempRes as $tmp) {
            $res[] = $tmp;
        }
        
        $constraintProcessor->setPersistedObjects($res);
        $rees = $constraintProcessor->process($constraint);

        return $rees;

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

        $this->virtualString->close();  

    }
    
    public function setInvalid(PObjectId $objectId) {
        $this->virtualString = new VirtualWriteString($this->fileName, $this->chunkSize);
        
        $this->virtualString->open();

        $objectStartPos = $this->getEndOfClassSection();

                    
        $object = false;
        while (true) {
            

            
            if ($this->isEndOfFile($objectStartPos)) {
                break;
            }

            
            $nextObjectId = $this->getNextObjectId($objectStartPos);
            if (!$this->isInvalid($nextObjectId) && $nextObjectId == $objectId->getId()) {

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
