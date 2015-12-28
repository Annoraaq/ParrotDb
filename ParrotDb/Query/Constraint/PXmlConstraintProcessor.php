<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\Query\PResultSet;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PClassConstraint;

/**
 * Description of PXmlConstraintProcessor
 *
 * @author J. Baum
 */
class PXmlConstraintProcessor implements PConstraintProcessor {
    
    /**
     * @var array List of persisted objects to scan. 
     */
    protected $persistedObjects = [];
    
    protected $constraint;
    
    /**
     * @param array $persistedObjects
     */
    public function setPersistedObjects($persistedObjects) {
        $this->persistedObjects = $persistedObjects;
    }
    
    /**
     * @inheritDoc
     */
    public function process(PConstraint $constraint) {
        $resultSet = new PResultSet();
        $counter = 0;
        foreach ($this->persistedObjects as $persistedObject) {
            if ($constraint->isSatisfiedBy($persistedObject)) {

                $resultSet->add($persistedObject);
                $counter++;
                
                if (!$this->checkAmount($constraint, $counter)) {
                    break;
                }
            }
        }
        
        return $this->order($constraint, $resultSet);
        
        
    }
    
    /**
     * Returns an ordered resultset.
     * 
     * @param PClassConstraint $constraint
     * @param PResultSet $resultSet
     * @return PResultSet
     */
    private function order(PClassConstraint $constraint, PResultSet $resultSet) {
        if (!isset($constraint->getOrderAttributes()[0])) {
            return $resultSet;
        }

        $this->constraint = $constraint;
        $resArr = $resultSet->getResultArray();

        if ($constraint->getOrder() == PClassConstraint::ORDER_DESC) {
            usort($resArr,
             array('\ParrotDb\Query\Constraint\PXmlConstraintProcessor',
                'cmpDesc'));
        } else {
            usort($resArr,
             array('\ParrotDb\Query\Constraint\PXmlConstraintProcessor',
                'cmpAsc'));
        }

        $resSet = new PResultSet();
        foreach ($resArr as $entry) {
            $resSet->add($entry);
        }

        return $resSet;
    }
    
    /**
     * Compares two objects descending based on the constraint's order settings.
     * 
     * @param type $a
     * @param type $b
     * @return int
     */
    public function cmpDesc($a, $b) {
        foreach ($this->constraint->getOrderAttributes() as $attr) {
            if ($a->getAttribute($attr)->getValue() 
                    < $b->getAttribute($attr)->getValue()) {
                return 1;
            } else if ($a->getAttribute($attr)->getValue()
                    > $b->getAttribute($attr)->getValue()) {
                return -1;
            }
        }
        
        return 0;
    }
    
    /**
     * Compares two objects ascending based on the constraint's order settings.
     * 
     * @param type $a
     * @param type $b
     * @return int
     */
    public function cmpAsc($a, $b) {
        foreach ($this->constraint->getOrderAttributes() as $attr) {
            if ($a->getAttribute($attr)->getValue()
                > $b->getAttribute($attr)->getValue()) {
                return 1;
            } else if ($a->getAttribute($attr)->getValue()
                < $b->getAttribute($attr)->getValue()) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * Checks, if amount is in given range.
     * 
     * @param PConstraint $constraint
     * @param int $counter
     * @return boolean
     */
    private function checkAmount(PConstraint $constraint, $counter) {
        if (($constraint instanceof PClassConstraint)
            && ($constraint->getAmount() > 0)
            && ($counter >= $constraint->getAmount())) {
            return false;
        }

        return true;
    }

}
