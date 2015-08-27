<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;

/**
 * Description of PConstraint
 *
 * @author J. Baum
 */
interface PConstraint {
    
    /**
     * Checks, whether the given object satisfies the constraint.
     * 
     * @param PObject $object
     * @return boolean
     */
    public function isSatisfiedBy(PObject $object);
    
}
