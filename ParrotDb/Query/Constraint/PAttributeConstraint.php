<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\Core\PException;

/**
 * Description of PAttributeConstraint
 *
 * @author J. Baum
 */
class PAttributeConstraint implements PConstraint {
    
    const OP_EQ = "=";
    const OP_LT = "<";
    const OP_GT = ">";
    const OP_LTE = "<=";
    const OP_GTE = ">=";
    
    /**
     * @var string Name of the attribute.
     */
    protected $name;
    
    /**
     * @var mixed Value of the attribute.
     */
    protected $value;
    
    /**
     * @var string Operator for the constraint.
     */
    protected $operator;
    
    /**
     * @param string $name Name of the attribute.
     * @param mixed $value Value of the attribute.
     * @param string $operator Operator for the constraint.
     */
    public function __construct($name, $value, $operator = self::OP_EQ) {
        $this->name = $name;
        $this->value = $value;
        $this->operator = $operator;
    }
    
    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $pObject) {
        

        if ($this->operator == self::OP_EQ) {
            return ($pObject->getAttribute($this->name)
                    ->getValue() == $this->value);
        } else if ($this->operator == self::OP_LT) {
            return ($pObject->getAttribute($this->name)
                    ->getValue() < $this->value);
        } else if ($this->operator == self::OP_GT) {
            return ($pObject->getAttribute($this->name)
                    ->getValue() > $this->value);
        } else if ($this->operator == self::OP_LTE) {
            return ($pObject->getAttribute($this->name)
                    ->getValue() <= $this->value);
        } else if ($this->operator == self::OP_GTE) {
            return ($pObject->getAttribute($this->name)
                    ->getValue() >= $this->value);
        } else {
            throw new PException(
                "The given operator `"
                . $this->operator
                . "` is not known."
            );
        }
    }

}
