<?php

namespace ParrotDb\Query\LotB\Ebnf;

/**
 * Description of OrSymbol
 *
 * @author J. Baum
 */
class OrSymbol implements Symbol {
    
    protected $symbols;
    
    public function addSymbol($symbol) {
        $this->symbols[] = $symbol;
    }
    
    public function getSymbols() {
        return $this->symbols;
    }
    
    public function isValid($array) {
        
    }
    
}
