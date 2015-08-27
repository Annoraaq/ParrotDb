<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\Persistence\Database;
use \ParrotDb\Query\Constraint\PConstraint;

/**
 * Description of PRelationConstraint
 *
 * @author J. Baum
 */
class PRelationConstraint implements PConstraint {
    
    const OP_EQ = "=";
    const OP_LT = "<";
    const OP_GT = ">";
    const OP_LTE = "<=";
    const OP_GTE = ">=";
    
    /**
     * @var string Name of the relation attribute.
     */
    protected $attributeName;
    
    /**
     * @var string Name of the class of the constraint. 
     */
    protected $className;
    
    /**
     * @var string Operator for the amount constraint.
     */
    protected $amountOperator;
    
    /**
     * @var int Value for the amount constraint.
     */
    protected $amountOperand;
    
    /**
     * @var PConstraint Constraint for the relation. 
     */
    protected $constraint;
    
    /**
     * @var Database
     */
    protected $database;
    
    /**
     * @param Database $database
     * @param string $attributeName
     * @param string $className
     * @param string $amountOperator
     * @param int $amountOperand
     * @param PConstraint $constraint
     */
    public function __construct(Database $database, $attributeName, $className,
     $amountOperator, $amountOperand, PConstraint $constraint) {
        $this->attributeName = $attributeName;
        $this->className = $className;
        $this->amountOperator = $amountOperator;
        $this->amountOperand = $amountOperand;
        $this->constraint = $constraint;
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $pObject) {
        
        $relObjects = [];
        foreach ($pObject->getAttribute($this->attributeName)->getValue() as $oid) {
            $relObjects[] = $this->database->fetch($oid);
        }
        
        $amount = 0;

        foreach ($relObjects as $relObj) {
            if (($relObj->getClass()->getName() == $this->className)
                && $this->constraint->isSatisfiedBy($relObj)) {
                $amount++;
            }
    
        }
        
        if ($this->amountOperator == self::OP_LT) {
            return ($amount < $this->amountOperand);
        } else if ($this->amountOperator == self::OP_GT) {
            return ($amount > $this->amountOperand);
        } else if ($this->amountOperator == self::OP_GTE) {
            return ($amount >= $this->amountOperand);
        } else if ($this->amountOperator == self::OP_LTE) {
            return ($amount <= $this->amountOperand);
        } else if ($this->amountOperator == self::OP_EQ) {
            return ($amount == $this->amountOperand);
        }

        return false;
        
    }

}
