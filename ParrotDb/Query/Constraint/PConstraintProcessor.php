<?php

namespace ParrotDb\Query\Constraint;

/**
 * Description of PConstraintProcessor
 *
 * @author J. Baum
 */
interface PConstraintProcessor {
    
    /**
     * Processes a constraint and returns a result set
     * 
     * @param PConstraint constraint object
     * @return PResultSet
     */
    public function process(PConstraint $constraint);
}
