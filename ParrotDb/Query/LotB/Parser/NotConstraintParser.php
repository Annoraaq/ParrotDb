<?php

namespace ParrotDb\Query\LotB\Parser;

use \ParrotDb\Query\Constraint\PNotConstraint;
use \ParrotDb\Utils\PUtils;

/**
 * Description of NotConstraintParser
 *
 * @author J. Baum
 */
class NotConstraintParser {
    
    /**
     * @var Parser 
     */
    protected $mainParser;
    
    /**
     * @var array
     */
    protected $exploded;
    
    /**
     * @param Parser $mainParser
     */
    public function __construct(Parser $mainParser) {
        $this->mainParser = $mainParser;
    }
    
    /**
     * @param array $exploded
     * @return PNotConstraint
     */
    public function parse($exploded) {
        $this->exploded = $exploded;
        
        $this->checkValidity();
        
        return new PNotConstraint(
            $this->mainParser->parseExploded(
                PUtils::cutArrayFront(
                    $this->exploded,
                    1
                )
            )
        );
    }
    
    /**
     * Checks whether the input is valid.
     * 
     * @throws PException
     */
    private function checkValidity() {
        if (!isset($this->exploded[1])) {
            throw new PException("Invalid LotB query.");
        }
    }
    
}
