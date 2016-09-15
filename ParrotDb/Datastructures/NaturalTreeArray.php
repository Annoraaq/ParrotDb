<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 07.09.2016
 * Time: 17:37
 */

namespace ParrotDb\Datastructures;

/**
 * Class NaturalTree
 *
 * Natural search tree implementation based on integer keys and string values. A tree is represented as an array.
 * @package ParrotDb\Datastructures
 */
class NaturalTreeArray extends NaturalTree
{
    protected function createTree()
    {
        return new PMemoryArray();
    }
}