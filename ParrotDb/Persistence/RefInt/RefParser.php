<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 07.09.2016
 * Time: 09:15
 */

namespace ParrotDb\Persistence\RefInt;

use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Persistence\Database;
use \ParrotDb\Persistence\LowLevelParser;
use ParrotDb\Utils\VirtualString;
use ParrotDb\Utils\VirtualWriteString;


abstract class RefParser extends LowLevelParser
{

    const INVALID_COUNTER = '0000000000';

    protected $database;

    protected abstract function getFileName();

    public function __construct(Database $database) {
        $this->database = $database;
        $this->createFile();
    }

    protected function createFile() {
        $filePath = $this->database->getPath() . '/' . $this->getFileName();
        if (!file_exists($filePath)) {

            $virtStr = new VirtualWriteString($filePath, $this->database->getConfig()->getChunkSize());
            $virtStr->open();
            $virtStr->append("(" . self::INVALID_COUNTER . ")");
            $virtStr->close();
        }
    }

    protected function findObject($objId) {

        $filePath = $this->database->getPath() . '/' . $this->getFileName();

        if (!file_exists($filePath)) {
            return -1;
        }

        $virtStr = new VirtualString($filePath, $this->database->getConfig()->getChunkSize());
        $virtStr->open();

        $currPos = 0;
        $valid = true;
        $first = true;
        while ($valid) {
            $startPos = $virtStr->findFirst("[", $currPos);

            $valid = ($startPos > -1) && (($currPos != $startPos) || $first === true);

            if ($valid) {
                $first = false;
                $oid = $virtStr->getNextInterval($startPos,"[", ",");

                if ((substr($oid,0,1) != "i") && ((int) $oid == $objId)) {
                    return $startPos;
                }

                $currPos = $virtStr->findFirst("]", $startPos);
            }
        }

        $virtStr->close();

        return -1;
    }

    public function loadRef($referrer) {
        $filePath = $this->database->getPath() . '/' . $this->getFileName();
        if (!file_exists($filePath)) {
            $this->createFile();
            return [];
        }

        $startPos = $this->findObject($referrer, $this->getFileName());

        if ($startPos > -1) {
            $virtStr = new VirtualWriteString($filePath, $this->database->getConfig()->getChunkSize());
            $virtStr->open();

            $lst = explode(",", $virtStr->getNextInterval($startPos, ",", "]"));

            $ret = [];
            foreach ($lst as $en) {
                $ret[$en] = $en;
            }

            $virtStr->close();

            return $ret;
        }

        return [];
    }

    /**
     * Remove all invalid entries
     */
    public function clean() {

        $filename = $this->database->getPath() . '/' . $this->getFileName();
        $this->virtualString = new VirtualWriteString(
            $filename,
            $this->database->getConfig()->getChunkSize()
        );

        $newVirtStr = new VirtualWriteString(
            $filename . ".temp",
            $this->database->getConfig()->getChunkSize()
        );

        $newVirtStr->open();

        $this->virtualString->open();

        $invalidInitStr = "(" . self::INVALID_COUNTER . ")";
        $newVirtStr->append($invalidInitStr);

        $currPos = 0;
        $valid = true;
        $first = true;
        while ($valid) {
            $startPos = $this->virtualString->findFirst("[", $currPos);

            $valid = ($startPos > -1) && (($currPos != $startPos) || $first === true);

            if ($valid) {
                $first = false;
                $oid = $this->virtualString->getNextInterval($startPos,"[", ",");

                if ((substr($oid,0,1) != "i")) {
                    $this->objectStartPos = $startPos;
                    $object = "[" . $this->getNextObject() . "]";
                    $newVirtStr->append($object);
                }

                $currPos = $this->virtualString->findFirst("]", $startPos);
            }
        }


        $newVirtStr->close();

        $this->virtualString->close();

        unlink($filename);
        rename($filename . ".temp", $filename);

    }

    protected function saveRef($referee, $list) {

        $startPos = $this->findObject($referee, $this->getFileName());

        $filePath = $this->database->getPath() . '/' . $this->getFileName();
        $virtStr = new VirtualWriteString($filePath, $this->database->getConfig()->getChunkSize());
        $virtStr->open();

        $counter = (int) $virtStr->substr(1, 11);

        if ($startPos > -1) {
            $virtStr->replace($startPos+1);
            $counter++;
            $virtStr->replaceStr(1, str_pad($counter, 10, '0', STR_PAD_LEFT));
            if (count($list) > 0) {
                $virtStr->append("[" . $referee . "," . implode($list, ",") . "]");
            }

        } else {
            if (count($list) > 0) {
                $virtStr->append("[" . $referee . "," . implode($list, ",") . "]");
            }
        }


        $virtStr->close();


        if ($counter > $this->database->getConfig()->getCleanThreshold()) {
            $this->clean();
        }

    }

    /**
     * @param PObjectId $oid1
     * @param PObjectId $oid2
     */
    public function saveRelation(PObjectId $oid1, PObjectId $oid2)
    {
        $list = $this->loadRef($oid2->getId());
        $hasChanged = !isset($list[$oid1->getId()]);
        $list[$oid1->getId()] = $oid1->getId();

        if ($hasChanged) {
            $this->saveRef($oid2->getId(), $list);
        }
    }

    /**
     * @param PObjectId $toDelete
     */
    public function removeRelation(PObjectId $toDelete) {


        $startPos = $this->findObject($toDelete->getId(), $this->getFileName());

        if ($startPos > -1) {
            $filePath = $this->database->getPath() . '/' . $this->getFileName();
            $virtStr = new VirtualWriteString($filePath, $this->database->getConfig()->getChunkSize());
            $virtStr->open();

            $counter = (int) $virtStr->substr(1, 11);
            $counter++;

            $virtStr->replace($startPos+1);

            if ($counter > $this->database->getConfig()->getCleanThreshold()) {
                $virtStr->close();
                $this->clean();
            } else {
                $virtStr->replaceStr(1, str_pad($counter, 10, '0', STR_PAD_LEFT));
                $virtStr->close();
            }
        }

    }

    public function getInvalid() {
        $virtualString = new VirtualWriteString(
            $this->database->getPath() . '/' . $this->getFileName(),
            $this->database->getConfig()->getChunkSize()
        );

        $virtualString->open();

        $cnt = (int) $virtualString->substr(1, 11);

        $virtualString->close();

        return $cnt;
    }
}