<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 20.06.2016
 * Time: 23:23
 */

namespace ParrotDb\Persistence\RefInt;

use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Utils\VirtualWriteString;
use ParrotDb\Utils\VirtualString;

/**
 * Class MemoryRefManager
 * @package Persistence
 *
 * Manages the map from an object id to all referring object ids
 */
class MemoryRefManager
{


    private function loadRefBy($referee) {
        return [];
    }

    private function findObject($objId, $filename) {
        return -1;
    }
    private function saveRefBy($referee, $list) {

    }

    private function loadRefList(PObjectId $referrer) {
        return [];
    }

    private function saveRefList(PObjectId $referrer, $list) {

    }

    /**
     * @param PObjectId $referrer
     * @param PObjectId $referee
     */
    public function addRefByRelation(PObjectId $referrer, PObjectId $referee) {

    }


    /**
     * @param PObjectId $toDelete
     */
    public function removeRefByRelations(PObjectId $toDelete) {



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