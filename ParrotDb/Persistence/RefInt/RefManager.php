<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 20.06.2016
 * Time: 23:23
 */

namespace ParrotDb\Persistence\RefInt;

use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Persistence\Database;
use \ParrotDb\Persistence\LowLevelParser;

/**
 * Class RefManager
 * @package Persistence
 *
 * Manages the map from an object id to all referring object ids
 */
class RefManager extends LowLevelParser
{

    private $refByParser;

    private $refListParser;

    public function __construct(Database $database) {
        $this->refByParser = new RefByParser($database);
        $this->refListParser = new RefListParser($database);
    }

    /**
     * @param PObjectId $referrer
     * @param PObjectId $referee
     */
    public function addRefByRelation(PObjectId $referrer, PObjectId $referee)
    {
        $this->refByParser->saveRelation($referrer, $referee);
        $this->refListParser->saveRelation($referee, $referrer);
    }


    /**
     * @param PObjectId $toDelete
     */
    public function removeRefByRelations(PObjectId $toDelete) {

        $refList = $this->refListParser->loadRef($toDelete->getId());

        $this->refByParser->removeRelationList($toDelete, $refList);

        $this->refListParser->removeRelation($toDelete);
    }


    /**
     * @param PObjectId $referee
     * @return mixed
     */
    public function getRefBy(PObjectId $referee) {
        return $this->refByParser->loadRef($referee->getId());
    }

    /**
     * @param PObjectId $referrer
     * @return mixed
     */
    public function getRefList(PObjectId $referrer) {
        return $this->refListParser->loadRef($referrer->getId());
    }

    /**
     *  Gets the amount of invalid objects from refby file
     *  @return int Amount of invalid objects
     */
    public function getRefByInvalid() {
        return $this->refByParser->getInvalid();
    }

    /**
     *  Gets the amount of invalid objects from reflist file
     *  @return int Amount of invalid objects
     */
    public function getRefListInvalid() {
        return $this->refListParser->getInvalid();
    }


    /**
     * Remove all invalid entries
     */
    public function cleanRefBy() {
        return $this->refByParser->clean();
    }

    /**
     * Remove all invalid entries
     */
    public function cleanRefList() {

        return $this->refListParser->clean();
    }




}
