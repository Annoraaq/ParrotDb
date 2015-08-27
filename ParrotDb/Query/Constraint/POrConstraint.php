<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;

/**
 * Description of PAndConstraint
 *
 * @author J. Baum
 */
class POrConstraint implements PConstraint {
    
    /**
     *
     * @var array Array of constraints to be connected by disjunction. 
     */
    protected $constraints = [];
    
    /**
     * 
     * @param array $constraints Array of constraints to
     * be connected by disjunction. 
     */
    public function __construct($constraints) {
        $this->constraints = $constraints;
    }
    
    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $pObject) {
        foreach ($this->constraints as $constraint) {
            if ($constraint->isSatisfiedBy($pObject)) {
                return true;
            }
        }
        
        return false;
    }

}
