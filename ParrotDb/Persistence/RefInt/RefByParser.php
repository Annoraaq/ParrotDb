<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 07.09.2016
 * Time: 09:17
 */

namespace ParrotDb\Persistence\RefInt;

use ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Persistence\Database;
use ParrotDb\Utils\VirtualString;
use ParrotDb\Utils\VirtualWriteString;


class RefByParser extends RefParser
{
    const FILE_NAME = 'refby.ref';

    /**
     * @param PObjectId $toDelete
     * @param $refList
     */
    public function removeRelation(PObjectId $toDelete, $refList) {

        foreach ($refList as $referee) {
            $refBy = $this->loadRef((int) $referee);
            unset($refBy[$toDelete->getId()]);
            $this->saveRef($referee, $refBy);
        }

        parent::removeRelation($toDelete);

    }


    protected function getFileName()
    {
        return self::FILE_NAME;
    }
}