<?php

namespace ParrotDb\Query\LotB\Ebnf;

use \ParrotDb\Core\PException;

/**
 * Description of Terminal
 *
 * @author J. Baum
 */
class Terminal implements Symbol {
    
    protected $value;
    
    public function __construct($value) {
        $this->value = $value;
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function setValue($value) {
        $this->value = $value;
    }

    public function isValid($array) {
        if (count($array) > 1) {
            throw new PException(
                "Expecting a single terminal symbol. "
                . count($array)
                . "  given.");
        }
        if ($array[0] != $this->value) {
            throw new PException(
                "Invalid terminal symbol: "
                . $array[0])
                . ". Expected "
                . $this->value
                . ".";
        }

        return array($this->value);
    }

}
