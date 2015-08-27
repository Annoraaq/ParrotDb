<?php

namespace ParrotDb\Query\LotB\Ebnf;

/**
 * Description of Element
 *
 * @author J. Baum
 */
Interface Symbol {
    
    public function isValid($array);
    
}
