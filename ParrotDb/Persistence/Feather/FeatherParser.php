<?php

namespace ParrotDb\Persistence\Feather;

use ParrotDb\Utils\VirtualString;
use ParrotDb\Utils\VirtualWriteString;
use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Core\PException;
use ParrotDb\Query\Constraint\PConstraint;
use ParrotDb\Query\Constraint\PXmlConstraintProcessor;
use ParrotDb\Query\PResultSet;

/**
 * The Featherparser handles the parsing of a feather file
 *
 * @author J. Baum
 */
class FeatherParser
{

    private $virtualString;
    private $fileName;
    private $chunkSize;
    private $bufferManager;
    private $objectStartPos;
    private $memoryLimit;
    private $charactersRead;
    private $objectBuffer;
    private $resultAccumulator;
    private $constraintProcessor;

    /**
     * @param string $fileName
     * @param int chunkSize
     */
    public function __construct($fileName, $chunkSize = 10024)
    {
        $this->fileName = $fileName;
        $this->chunkSize = $chunkSize;
        $this->objectStartPos = 0;
        $this->memoryLimit = 10000000;
        $this->charactersRead = 0;
        $this->constraintProcessor = new PXmlConstraintProcessor();
    }

    /**
     * @param \ParrotDb\Persistence\Feather\FeatherBufferManager $bufferManager
     */
    public function setBufferManager(FeatherBufferManager $bufferManager)
    {
        $this->bufferManager = $bufferManager;
    }

    /**
     * @param int $position
     */
    private function notFound($position)
    {
        return ($position < 0);
    }

    private function getEndOfClassSection()
    {

        $endOfClass = $this->virtualString->findFirst(']');
        if ($this->notFound($endOfClass)) {

            $this->virtualString->close();
            throw new PException(
            "Malformed feather database file: "
            . $this->fileName . ": " . $this->virtualString->getWindow()
            );
        }

        return $endOfClass;
    }

    private function isEndOfFile($position)
    {
        return $this->notFound($this->virtualString->findFirst("[", $position));
    }

    private function isInvalid($objectId)
    {
        return (isset($objectId[0]) && $objectId[0] == "i");
    }

    private function openVirtualString()
    {
        $this->virtualString = new VirtualString(
            $this->fileName, $this->chunkSize
        );
        $this->virtualString->open();

        $this->objectStartPos = $this->getEndOfClassSection();
    }

    /**
     * @param PObjectId $objectId
     * @return boolean
     */
    public function isObjectStoredIn(PObjectId $objectId)
    {
        return ($this->traverse($objectId) !== false);
    }

    private function traverse(PObjectId $objectId)
    {
        $this->openVirtualString();

        $object = false;
        while (true) {
            if ($this->isEndOfFile($this->objectStartPos)) {
                break;
            }
            $nextObjectId = $this->getNextObjectId();
            if (!$this->isInvalid($nextObjectId) && $nextObjectId == $objectId->getId()) {
                $object = "[" . $this->getNextObject() . "]";
                break;
            } else {
                $this->objectStartPos = $this->getNextObjectPosition(
                    mb_strlen($nextObjectId)
                );
            }
        }

        $this->virtualString->close();

        return $object;
    }

    private function getNextObjectId()
    {
        $oid = $this->virtualString->getNextInterval(
            $this->objectStartPos, "[", ","
        );

        return $oid;
    }

    private function getNextObject()
    {
        return $this->virtualString->getNextInterval(
                $this->objectStartPos, "[", "]"
        );
    }

    private function getNextObjectPosition($idLen)
    {
        $lengthStart = $this->virtualString->findFirst(
            ",", $this->objectStartPos
        );
        $len = $this->virtualString->getNextInterval($lengthStart, ",", ",");
        $lenOfLen = mb_strlen($len) + $idLen + 1;

        return
            $lengthStart + $len + $lenOfLen;
    }

    /**
     * @param PObjectId $objectId
     * @return boolean
     */
    public function fetch(PObjectId $objectId)
    {

        $object = $this->traverse($objectId);

        if ($object !== false) {
            $objectDeserializer = new FeatherObjectDeserializer(
                $this->getClass()
            );
            $objectDeserializer->setInput($object);
            return $objectDeserializer->deserialize();
        }
        return false;
    }

    private function getBuffered(PConstraint $constraint)
    {
        $constraintProcessor = new PXmlConstraintProcessor();
        $constraintProcessor->setPersistedObjects(
            $this->bufferManager->getBuffer($this->fileName)
        );

        return $constraintProcessor->process($constraint);
    }

    private function isCompletelyBuffered()
    {
        return $this->bufferManager->isWholeFileInBuffer($this->fileName);
    }

    private function bufferExists()
    {
        return ($this->bufferManager->getBufferOffset($this->fileName) > 0);
    }

    private function loadBuffer()
    {
        $objects = array();
        if ($this->bufferExists()) {
            $objects = $this->bufferManager->getBuffer($this->fileName);
            $this->objectStartPos = $this->bufferManager->getBufferOffset(
                $this->fileName
            );
        } else {
            $this->objectStartPos = $this->getEndOfClassSection();
            $this->bufferManager->setBufferOffset(
                $this->fileName, $this->objectStartPos
            );
        }

        return $objects;
    }

    private function accumulate($data)
    {
        foreach ($data as $tmp) {
            $this->resultAccumulator[] = $tmp;
        }
    }

    private function processBuffer(PConstraint $constraint)
    {
        if ($this->charactersRead > $this->memoryLimit) {
            $this->constraintProcessor->setPersistedObjects($this->objectBuffer);
            $this->accumulate($this->constraintProcessor->process($constraint));

            $this->charactersRead = 0;
            $this->objectBuffer = array();
        }
    }

    private function isSpaceInBuffer($objLen)
    {
        $neededSpace = $this->bufferManager->getBufferOffset($this->fileName) + $objLen;
        return ($neededSpace <= $this->memoryLimit);
    }

    private function isValidObject($oid)
    {
        return (!isset($oid[0]) || $oid[0] != 'i');
    }

    private function updateBuffer($objectDeserializer, $obj, $objLen)
    {
        $objectDeserializer->setInput($obj);
        $temp = $objectDeserializer->deserialize();

        if ($this->isSpaceInBuffer($objLen)) {
            $this->bufferManager->setWholeFileInBuffer($this->fileName, true);
            $this->bufferManager->setBufferOffset(
                $this->fileName,
                $this->bufferManager->getBufferOffset($this->fileName) + $objLen
            );
            $this->bufferManager->addToBuffer($this->fileName, $temp);
        } else {
            $this->bufferManager->setWholeFileInBuffer($this->fileName, false);
        }
        $this->objectBuffer[] = $temp;
    }

    private function processEntry(FeatherObjectDeserializer $objectDeserializer)
    {
        $obj = "[" . $this->getNextObject() . "]";
        $objLen = mb_strlen($obj);
        $this->charactersRead += $objLen;

        $oid = $this->getNextObjectId();
        if ($this->isValidObject($oid)) {
            $this->updateBuffer($objectDeserializer, $obj, $objLen);
        }

        $this->objectStartPos = $this->getNextObjectPosition(
            mb_strlen($oid)
        );
    }

    /**
     * @param PConstraint $constraint
     * @return PResultSet
     */
    public function fetchConstraint(PConstraint $constraint)
    {

        $this->resultAccumulator = array();

        if ($this->isCompletelyBuffered()) {
            return $this->getBuffered($constraint);
        }

        $objectDeserializer = new FeatherObjectDeserializer($this->getClass());
        $this->openVirtualString();

        $this->objectBuffer = $this->loadBuffer();

        $this->charactersRead = 0;

        while (true) {
            if ($this->isEndOfFile($this->objectStartPos)) {
                break;
            }

            $this->processBuffer($constraint);
            $this->processEntry($objectDeserializer);
        }

        $this->virtualString->close();

        $this->constraintProcessor->setPersistedObjects($this->objectBuffer);
        $this->accumulate($this->constraintProcessor->process($constraint));

        $this->constraintProcessor->setPersistedObjects($this->resultAccumulator);
        return $this->constraintProcessor->process($constraint);
    }

    /**
     * @return PClass
     */
    public function getClass()
    {
        $this->openVirtualString();
        $classDeserializer = new FeatherClassDeserializer();
        $input = "c" . $this->virtualString->getNextInterval(0, "c[", "]") . "]";
        $classDeserializer->setInput($input);
        $class = $classDeserializer->deserialize();
        $this->virtualString->close();

        return $class;
    }

    /**
     * @param PObjectId $objectId
     * @return boolean
     */
    public function setInvalid(PObjectId $objectId)
    {
        $this->virtualString = new VirtualWriteString(
            $this->fileName, $this->chunkSize
        );

        $this->virtualString->open();

        $this->objectStartPos = $this->getEndOfClassSection();

        while (true) {
            if ($this->isEndOfFile($this->objectStartPos)) {
                break;
            }

            $nextObjectId = $this->getNextObjectId();
            if (!$this->isInvalid($nextObjectId)
                && $nextObjectId == $objectId->getId()) {
                $this->virtualString->replace($this->objectStartPos + 2, "j");
                break;
            } else {
                $this->objectStartPos = $this->getNextObjectPosition(
                    mb_strlen($nextObjectId)
                );
            }
        }

        $this->virtualString->close();

        return false;
    }

}
