<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;

/**
 * Description of PAndConstraint
 *
 * @author J. Baum
 */
class PAndConstraint implements PConstraint {
    
    const OP_AND = ",";
    const OP_OR = "or";
    
    /**
     *
     * @var array Constraints connected by conjunction
     */
    protected $constraints = [];
    
    /**
     * 
     * @param array $constraints Constraints connected by conjunction
     */
    public function __construct($constraints) {
        $this->constraints = $constraints;
    }
    
//    public function addConstraint($operation, PConstraint $constraint) {
//        $this->constraints[] = array($operation, $constraint);
//    }
    
    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $pObject) {

        foreach ($this->constraints as $constraint) {

            if (!$constraint->isSatisfiedBy($pObject)) {
                return false;
            }
        }
        
        return true;
    }

}
