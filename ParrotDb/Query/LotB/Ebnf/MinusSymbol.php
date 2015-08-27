<?php

namespace ParrotDb\Query\LotB\Ebnf;

/**
 * Description of MinusSymbol
 *
 * @author J. Baum
 */
class MinusSymbol implements Symbol {
    
    protected $symbol;
    protected $minusSymbol;
    
    public function __construct(Symbol $symbol, Symbol $minusSymbol) {
        $this->symbol = $symbol;
        $this->minusSymbol = $minusSymbol;
    }
    
    public function setSymbol(Symbol $symbol) {
        $this->symbol = $symbol;
    }
    
    public function getSymbol() {
        return $this->symbol;
    }
    
    public function setMinusSymbol(Symbol $minusSymbol) {
        $this->minusSymbol = $minusSymbol;
    }
    
    public function getMinusSymbol() {
        return $this->minusSymbol;
    }

    public function isValid($array) {
        
    }

}
