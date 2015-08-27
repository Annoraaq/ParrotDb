<?php

namespace ParrotDb\Query;

use \ParrotDb\Core\PException;

/**
 * Description of PResultSet
 *
 * @author J. Baum
 */
class PResultSet implements \Iterator {

    /**
     * @var array Result array.
     */
    protected $results = [];
    
    /**
     * @var int Size of the set.
     */
    protected $size = 0;

    /**
     * Adds a new item to the result set.
     * 
     * @param mixed $result
     */
    public function add($result) {
        $this->results[] = $result;
        $this->size++;
    }
    
    public function getResultArray() {
        return $this->results;
    }

    /**
     * Returns the first item from the result set.
     * 
     * @return mixed
     * @throws PException
     */
    public function first() {
        if (count($this->results) == 0) {
            throw new PException("Result set is empty.");
        }

        return $this->results[0];
    }

    /**
     * @inheritDoc
     */
    public function current() {
        return current($this->results);
    }

    /**
     * @inheritDoc
     */
    public function key() {
        return key($this->results);
    }

    /**
     * @inheritDoc
     */
    public function next() {
        return next($this->results);
    }

    /**
     * @inheritDoc
     */
    public function rewind() {
        reset($this->results);
    }

    /**
     * @inheritDoc
     */
    public function valid() {
        return ($this->current() !== false);
    }
    
    /**
     * Returns the amount of elements in the result set.
     * 
     * @return int
     */
    public function size() {
        return $this->size;
    }

}
