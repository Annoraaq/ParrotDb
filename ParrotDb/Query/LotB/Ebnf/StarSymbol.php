<?php

namespace ParrotDb\Query\LotB\Ebnf;

/**
 * Description of StarSymbol
 *
 * @author J. Baum
 */
class StarSymbol implements Symbol {
    
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
