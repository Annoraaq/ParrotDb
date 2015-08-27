<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;

/**
 * Description of PEmptyConstraint
 *
 * @author J. Baum
 */
class PEmptyConstraint implements PConstraint {
    
    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $object) {
        return true;
    }

}
