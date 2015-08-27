<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;

/**
 * Description of PNotConstraint
 *
 * @author J. Baum
 */
class PNotConstraint implements PConstraint {
    
    /**
     * @var PConstraint Constrint to be negated. 
     */
    protected $constraint;
    
    /**
     * @param PConstraint $constraint Constrint to be negated.
     */
    public function __construct($constraint) {
        $this->constraint = $constraint;
    }
    
    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $pObject) {
       return (!$this->constraint->isSatisfiedBy($pObject));
    }

}
