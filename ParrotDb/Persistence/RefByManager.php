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
class RefByManager
{


    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    private function loadRefBy($referee) {
        $filePath = $this->database->getPath() . '/refby.ref';
        if (!file_exists($filePath)) {
            return [];
        }

        $startPos = $this->findObject($referee, 'refby.ref');

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

        $startPos = $this->findObject($referee, 'refby.ref');

        $filePath = $this->database->getPath() . '/refby.ref';
        $virtStr = new VirtualWriteString($filePath, 10024);
        $virtStr->open();

        if ($startPos > -1) {
            $virtStr->replaceStr($startPos + 2 + mb_strlen($referee), implode($list, ","));
        } else {
            $virtStr->append("[" . $referee . "," . implode($list, ",") . "]");
        }

        $virtStr->close();

    }

    private function loadRefList(PObjectId $referrer) {
        $filePath = $this->database->getPath() . '/reflist.ref';
        if (!file_exists($filePath)) {
            return [];
        }
        $startPos = $this->findObject($referrer->getId(), 'reflist.ref');

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
        $startPos = $this->findObject($referrer->getId(), 'reflist.ref');

        $filePath = $this->database->getPath() . '/reflist.ref';
        $virtStr = new VirtualWriteString($filePath, 10024);
        $virtStr->open();

        if ($startPos > -1) {
            //echo "safeRefList: " . ($startPos + 2 + mb_strlen($referrer->getId())) . ", " .  implode($list, ",") . PHP_EOL;
            $virtStr->replace(($startPos + 1),0);
            $virtStr->append("[" . $referrer->getId() . "," . implode($list, ",") . "]");
            //$virtStr->replaceStr($startPos + 2 + mb_strlen($referrer->getId()), implode($list, ","));
            //echo $virtStr->substr(0, 100) . PHP_EOL;
        } else {
           // echo "append: " . "[" . $referrer->getId() . "," . implode($list, ",") . "]" . PHP_EOL;
            $virtStr->append("[" . $referrer->getId() . "," . implode($list, ",") . "]");
        }

        $virtStr->close();
    }

    /**
     * @param PObjectId $referrer
     * @param PObjectId $referee
     */
    public function addRefByRelation(PObjectId $referrer, PObjectId $referee) {

        $list = $this->loadRefBy($referee->getId());

        $list[$referrer->getId()] = $referrer->getId();

        $this->saveRefBy($referee->getId(), $list);


        $list = $this->loadRefList($referrer);

        $list[$referee->getId()] = $referee->getId();

        $this->saveRefList($referrer, $list);
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

        $startPos = $this->findObject($toDelete->getId(), 'reflist.ref');

        if ($startPos > -1) {
            $filePath = $this->database->getPath() . '/reflist.ref';
            $virtStr = new VirtualWriteString($filePath, 10024);
            $virtStr->open();
            $virtStr->replace($startPos+1,0);

            $virtStr->close();
        }

        $startPos = $this->findObject($toDelete->getId(), 'refby.ref');

        if ($startPos > -1) {
            $filePath = $this->database->getPath() . '/refby.ref';
            $virtStr = new VirtualWriteString($filePath, 10024);
            $virtStr->open();
            $virtStr->replace($startPos+1,0);

            $virtStr->close();
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

}