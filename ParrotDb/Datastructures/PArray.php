<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 11.09.2016
 * Time: 17:09
 */

namespace ParrotDb\Datastructures;


interface PArray
{
    public function access($index);

    public function put($index, $value);

    public function remove($index);
}