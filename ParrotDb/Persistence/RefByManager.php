<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 20.06.2016
 * Time: 23:23
 */

namespace ParrotDb\Persistence;

use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Utils\VirtualWriteString;
use ParrotDb\Utils\VirtualString;

/**
 * Class RefByManager
 * @package Persistence
 *
 * Manages the map from an object id to all referring object ids
 */
class RefByManager extends LowLevelParser
{
    const REFBY_FILE_NAME = 'refby.ref';

    const REFLIST_FILE_NAME = 'reflist.ref';

    const INVALID_COUNTER = '0000000000';

    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
        $this->createFile(self::REFBY_FILE_NAME);
        $this->createFile(self::REFLIST_FILE_NAME);
    }

    private function createFile($fileName) {
        $filePath = $this->database->getPath() . '/' . $fileName;
        if (!file_exists($filePath)) {

            $virtStr = new VirtualWriteString($filePath, 10024);
            $virtStr->open();
            $virtStr->append("(" . self::INVALID_COUNTER . ")");
            $virtStr->close();
        }
    }

    private function loadRefBy($referee) {
        $filePath = $this->database->getPath() . '/' . self::REFBY_FILE_NAME;
        if (!file_exists($filePath)) {
            $this->createFile(self::REFBY_FILE_NAME);
            return [];
        }

        $startPos = $this->findObject($referee, self::REFBY_FILE_NAME);

        if ($startPos > -1) {
            $virtStr = new VirtualWriteString($filePath, 10024);
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

    private function findObject($objId, $filename) {

        $filePath = $this->database->getPath() . '/' . $filename;

        if (!file_exists($filePath)) {
            return -1;
        }

        $virtStr = new VirtualString($filePath, 10024);
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
    private function saveRefBy($referee, $list) {

        $startPos = $this->findObject($referee, self::REFBY_FILE_NAME);

        $filePath = $this->database->getPath() . '/' . self::REFBY_FILE_NAME;
        $virtStr = new VirtualWriteString($filePath, 10024);
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
            $this->cleanRefBy();
        }

    }

    private function loadRefList(PObjectId $referrer) {
        $filePath = $this->database->getPath() . '/' . self::REFLIST_FILE_NAME;
        if (!file_exists($filePath)) {
            $this->createFile(self::REFLIST_FILE_NAME);
            return [];
        }
        $startPos = $this->findObject($referrer->getId(), self::REFLIST_FILE_NAME);

        if ($startPos > -1) {
            $virtStr = new VirtualWriteString($filePath, 10024);
            $virtStr->open();

            $lst = [];
            if ($virtStr->hasNextInterval($startPos, ",", "]")) {
                $lst = explode(",", $virtStr->getNextInterval($startPos, ",", "]"));
            }

            $ret = [];
            foreach ($lst as $en) {
                $ret[$en] = $en;
            }

            $virtStr->close();

            return $ret;
        }

        return [];
    }

    private function saveRefList(PObjectId $referrer, $list) {
        $startPos = $this->findObject($referrer->getId(), self::REFLIST_FILE_NAME);

        $filePath = $this->database->getPath() . '/' . self::REFLIST_FILE_NAME;
        $virtStr = new VirtualWriteString($filePath, 10024);
        $virtStr->open();

        $counter = (int) $virtStr->substr(1, 11);

        if ($startPos > -1) {
            $virtStr->replace(($startPos + 1));
            $counter++;
            $virtStr->replaceStr(1, str_pad($counter, 10, '0', STR_PAD_LEFT));
            if (count($list) > 0) {
                $virtStr->append("[" . $referrer->getId() . "," . implode($list, ",") . "]");
            }
        } else {
            if (count($list) > 0) {
                $virtStr->append("[" . $referrer->getId() . "," . implode($list, ",") . "]");
            }
        }



        $virtStr->close();

        if ($counter > $this->database->getConfig()->getCleanThreshold()) {
            $this->cleanRefList();
        }
    }

    /**
     * @param PObjectId $referrer
     * @param PObjectId $referee
     */
    public function addRefByRelation(PObjectId $referrer, PObjectId $referee)
    {

        $list = $this->loadRefBy($referee->getId());

        $hasChanged = !isset($list[$referrer->getId()]);

        $list[$referrer->getId()] = $referrer->getId();

        if ($hasChanged) {
            $this->saveRefBy($referee->getId(), $list);
        }


        $list = $this->loadRefList($referrer);
        $hasChanged = !isset($list[$referee->getId()]);

        $list[$referee->getId()] = $referee->getId();

        if ($hasChanged) {
            $this->saveRefList($referrer, $list);
        }
    }


    /**
     * @param PObjectId $toDelete
     */
    public function removeRefByRelations(PObjectId $toDelete) {

        $refList = $this->loadRefList($toDelete);

        foreach ($refList as $referee) {
            $refBy = $this->loadRefBy((int) $referee);
            unset($refBy[$toDelete->getId()]);
            $this->saveRefBy($referee, $refBy);
        }

        $startPos = $this->findObject($toDelete->getId(), self::REFLIST_FILE_NAME);

        if ($startPos > -1) {
            $filePath = $this->database->getPath() . '/' . self::REFLIST_FILE_NAME;
            $virtStr = new VirtualWriteString($filePath, 10024);
            $virtStr->open();

            $counter = (int) $virtStr->substr(1, 11);
            $counter++;

            $virtStr->replace($startPos+1);

            if ($counter > $this->database->getConfig()->getCleanThreshold()) {
                $virtStr->close();
                $this->cleanRefList();
            } else {
                $virtStr->replaceStr(1, str_pad($counter, 10, '0', STR_PAD_LEFT));
                $virtStr->close();
            }


        }

        $startPos = $this->findObject($toDelete->getId(), self::REFBY_FILE_NAME);

        if ($startPos > -1) {
            $filePath = $this->database->getPath() . '/' . self::REFBY_FILE_NAME;
            $virtStr = new VirtualWriteString($filePath, 10024);
            $virtStr->open();


            $counter = (int) $virtStr->substr(1, 11);
            $counter++;

            $virtStr->replace($startPos+1);

            if ($counter > $this->database->getConfig()->getCleanThreshold()) {
                $this->cleanRefBy();
            } else {
                $virtStr->replaceStr(1, str_pad($counter, 10, '0', STR_PAD_LEFT));
                $virtStr->close();
            }
        }

    }


    /**
     * @param PObjectId $referee
     * @return mixed
     */
    public function getRefBy(PObjectId $referee) {
        return $this->loadRefBy($referee->getId());
    }

    /**
     * @param PObjectId $referrer
     * @return mixed
     */
    public function getRefList(PObjectId $referrer) {
        return $this->loadRefList($referrer);
    }

    /**
     *  Gets the amount of invalid objects from refby file
     *  @return int Amount of invalid objects
     */
    public function getRefByInvalid() {
        return $this->getInvalid(self::REFBY_FILE_NAME);
    }

    /**
     *  Gets the amount of invalid objects from reflist file
     *  @return int Amount of invalid objects
     */
    public function getRefListInvalid() {
        return $this->getInvalid(self::REFLIST_FILE_NAME);
    }

    private function getInvalid($fileName) {
        $virtualString = new VirtualWriteString(
            $this->database->getPath() . '/' . $fileName,
            $this->database->getConfig()->getChunkSize()
        );

        $virtualString->open();

        $cnt = (int) $virtualString->substr(1, 11);

        $virtualString->close();

        return $cnt;
    }

    /**
     * Remove all invalid entries
     */
    public function cleanRefBy() {

        $filename = $this->database->getPath() . '/' . self::REFBY_FILE_NAME;
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

    /**
     * Remove all invalid entries
     */
    public function cleanRefList() {

        $filename = $this->database->getPath() . '/' . self::REFLIST_FILE_NAME;
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




}