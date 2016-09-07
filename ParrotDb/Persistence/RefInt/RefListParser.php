<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 07.09.2016
 * Time: 09:17
 */

namespace ParrotDb\Persistence\RefInt;

use ParrotDb\ObjectModel\PObjectId;
use ParrotDb\Utils\VirtualWriteString;


class RefListParser extends RefParser
{

    const FILE_NAME = 'reflist.ref';


    protected function getFileName()
    {
        return self::FILE_NAME;
    }
}