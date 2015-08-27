<?php

namespace ParrotDb\Query\LotB\Ebnf;

/**
 * Description of OptionalSymbol
 *
 * @author J. Baum
 */
class OptionalSymbol implements Symbol {
    
    protected $symbol;
    
    public function __construct(Symbol $symbol) {
        $this->symbol = $symbol;
    }
    
    public function setSymbol(Symbol $symbol) {
        $this->symbol = $symbol;
    }
    
    public function getSymbol() {
        return $this->symbol;
    }

    public function isValid($array) {
        
    }

}
