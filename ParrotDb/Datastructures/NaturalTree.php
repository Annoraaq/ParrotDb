<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 11.09.2016
 * Time: 17:04
 */

namespace ParrotDb\Datastructures;


abstract class NaturalTree
{
    private $tree;
    private $lastPos;

    protected abstract function createTree();


    public function __construct()
    {
        $this->lastPos = 1;
    }

    public function add($key, $val)
    {
        $this->lastPos = -1;
        $this->findRec(1, $key);

        $this->getTree()->put($this->lastPos, "$key,$val");
    }

    public function find($key)
    {
        $this->lastPos = -1;
        $findRec = $this->findRec(1, $key);
        return $findRec;
    }

    public function remove($key)
    {
        $this->removeRec(0, $key);
    }


    public function printTree($entries)
    {
        $tree = "";

        for ($i = 1; $i <= $entries; $i++) {
            $tree .= $this->getVal($i) . ",";
        }

        return $tree;
    }

    protected function getTree() {

        if ($this->tree == null) {
            $this->tree = $this->createTree();
        }
        return $this->tree;
    }


    private function findRec($startNode, $key)
    {
        if ($this->isNull($startNode)) {
            $this->lastPos = $startNode;
            return false;
        } else if ($key < $this->getKey($startNode)) {
            return $this->findRec($this->leftChild($startNode), $key);
        } else if ($key > $this->getKey($startNode)) {
            return $this->findRec($this->rightChild($startNode), $key);
        } else {
            return $startNode;
        }
    }

    private function removeRec($startNode, $key)
    {
        if (!$this->isNull($startNode)) {
            return;
        } else if ($key < $this->getKey($startNode)) {
            $this->removeRec($this->leftChild($startNode), $key);
        } else if ($key > $this->getKey($startNode)) {
            $this->removeRec($this->rightChild($startNode), $key);
        } else if ($this->isNull($this->leftChild($startNode))) {
            $this->getTree()->put($startNode, $this->getTree()->access($this->rightChild($startNode)));
            $this->getTree()->remove($this->rightChild($startNode));
        } else if ($this->isNull($this->rightChild($startNode))) {
            $this->getTree()->put($startNode, $this->tree->access($this->leftChild($startNode)));
            $this->getTree()->remove($this->leftChild($startNode));
        } else {
            $q = $this->symDesc($startNode);

            if ($q == $startNode) {
                $this->getTree()->put($startNode, $this->getTree()->access($this->rightChild($q)));
                $this->getTree()->put($this->rightChild($q), $this->rightChild($this->rightChild($q)));
            } else {
                $this->getTree()->put($startNode, $this->getTree()->access($this->leftChild($q)));
                $this->getTree()->put($this->leftChild($q), $this->leftChild($this->leftChild($q)));
            }
        }
    }

    private function symDesc($node)
    {
        if ($this->isNull($this->leftChild($this->rightChild($node)))) {
            $node = $this->rightChild($node);
            while ($this->isNull($this->leftChild($this->leftChild($node)))) {
                $node = $this->leftChild($node);
            }
        }

        return $node;
    }

    private function leftChild($node)
    {
        return $node * 2;
    }

    private function rightChild($node)
    {
        return $node * 2 + 1;
    }

    private function isNull($node)
    {
        return false === $this->getTree()->access($node);
    }

    private function getKey($node)
    {
        return $this->splitPayload($this->getTree()->access($node))[0];
    }

    private function splitPayload($payload)
    {
        return explode(",", $payload);
    }

    private function getVal($node)
    {
        return $this->splitPayload($this->getTree()->access($node))[1];
    }

}