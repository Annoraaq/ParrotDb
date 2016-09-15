<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 07.09.2016
 * Time: 17:37
 */

namespace ParrotDb\Datastructures;

/**
 * Class NaturalTreeVirtArray
 *
 * Natural search tree implementation based on integer keys and string values. A tree is represented as an array,
 * written to a file.
 * @package ParrotDb\Datastructures
 */
class NaturalTreeVirtArray extends NaturalTree
{
    private $fileName;


    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function add($key, $val) {
        $this->getTree()->open();
        parent::add($key, $val);
        $this->getTree()->close();
    }

    public function find($key)
    {
        $this->getTree()->open();
        $elem = parent::find($key);
        $this->getTree()->close();

        return $elem;
    }

    public function remove($key)
    {
        $this->getTree()->open();
        parent::remove($key);
        $this->getTree()->close();
    }



    public function printTree($entries) {

        $this->getTree()->open();
        $tree = parent::printTree($entries);
        $this->getTree()->close();

        return $tree;
    }


    protected function createTree()
    {
        return new VirtualArray($this->fileName);
    }
}