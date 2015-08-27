<?php

namespace ParrotDb\Query\LotB\Ebnf;

/**
 * Description of NonTerminal
 *
 * @author J. Baum
 */
class NonTerminal implements Symbol {
    
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
