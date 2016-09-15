<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 11.09.2016
 * Time: 17:10
 */

namespace ParrotDb\Datastructures;


class PMemoryArray implements PArray
{
    private $arr = [];

    public function access($index)
    {
        if (!isset($this->arr[$index])) {
            return false;
        }
        return $this->arr[$index];
    }

    public function put($index, $value)
    {
        $this->arr[$index] = $value;
    }

    public function remove($index)
    {
        unset($this->arr[$index]);
    }
}