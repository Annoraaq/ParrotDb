<?php

namespace ParrotDb\Query\LotB\Parser;

use \ParrotDb\Query\LotB\Parser\ConstraintGroupParser;
use \ParrotDb\Utils\PUtils;

/**
 * Description of RemoveParanthesisParser
 *
 * @author J. Baum
 */
class ParanthesisParser extends ConstraintGroupParser {
    
    /**
     * @inheritDoc
     */
    protected function noGroupingConstraint() {
        return $this->mainParser->parseExploded(
          PUtils::cutArrayTail(
           PUtils::cutArrayFront(
            $this->exploded, 1
           )
          )
        );
    }
}
