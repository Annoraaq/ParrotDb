<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 06.09.2016
 * Time: 11:59
 */

namespace ParrotDb\Persistence;


use ParrotDb\Utils\VirtualString;

abstract class LowLevelParser {

    protected $virtualString;
    protected $objectStartPos;

    /**
     * @return string next object id
     */
    protected function getNextObjectId()
    {
        $oid = $this->virtualString->getNextInterval(
            $this->objectStartPos, "[", ","
        );

        return $oid;
    }

    /**
     * @return string next object
     */
    protected function getNextObject()
    {
        return $this->virtualString->getNextInterval(
            $this->objectStartPos, "[", "]"
        );
    }

    /**
     * @param $idLen
     * @return int
     */
    protected function getNextObjectPosition($idLen)
    {
        $lengthStart = $this->virtualString->findFirst(
            ",", $this->objectStartPos
        );
        $len = $this->virtualString->getNextInterval($lengthStart, ",", ",");


        // $lenOfLen = mb_strlen($len) + $idLen + 1;
        // :todo
        // check why this is independend from the length of the object id
        $lenOfLen = mb_strlen($len) + 1 + 1;
        return
            $lengthStart + $len + $lenOfLen;
    }

    /**
     * @param $position
     * @return bool
     */
    protected function notFound($position)
    {
        return ($position < 0);
    }

    protected function isEndOfFile($position)
    {
        return $this->notFound($this->virtualString->findFirst("[", $position));
    }

    protected function isInvalid($objectId)
    {
        return (isset($objectId[0]) && $objectId[0] == "i");
    }


}