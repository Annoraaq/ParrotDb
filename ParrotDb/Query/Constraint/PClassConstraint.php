<?php

namespace ParrotDb\Query\Constraint;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\Query\Constraint\PConstraint;

/**
 * Description of PClassConstraint
 *
 * @author J. Baum
 */
class PClassConstraint implements PConstraint {
    
    const ORDER_ASC = "<<";
    const ORDER_DESC = ">>";
    
    /**
     * @var string Name of the class
     */
    protected $className;
    
    /**
     * @var PConstraint Constraint for the objects.
     */
    protected $constraint;
    
    /**
     * @var int Limit of result set.
     */
    protected $amount;
    
    /**
     * @var string Ordering of the result set.
     */
    protected $order;
    
    /**
     * @var array Attributes to order by.
     */
    protected $orderAttributes;
    
    /**
     * @param string $className Name of the class
     * @param PConstraint $constraint Constraint for the objects.
     */
    public function __construct($className, PConstraint $constraint = null) {
        $this->className = $className;
        if ($constraint == null) {
            $this->constraint = new PEmptyConstraint();
        } else {
            $this->constraint = $constraint;
        }
        $this->amount = 0;
            
    }
    
    /**
     * @param int $amount
     */
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    
    /**
     * @param string $order
     */
    public function setOrder($order) {
        $this->order = $order;
    }
    
    /**
     * @return string
     */
    public function getOrder() {
        return $this->order;
    }
     
    /**
     * @param array $orderAttributes
     */
    public function setOrderAttributes($orderAttributes) {
        $this->orderAttributes = $orderAttributes;
    }
    
    /**
     * @param array $orderAttributes
     */
    public function getOrderAttributes() {
        return $this->orderAttributes;
    }
    
    /**
     * @return int
     */
    public function getAmount() {
        return $this->amount;
    }
    
    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(PObject $pObject) {
        return ($this->matchesClassName($pObject) &&
                $this->constraint->isSatisfiedBy($pObject));
    }
    
    private function matchesClassName(PObject $pObject) {
        if ($pObject->getClass()->getName() == $this->className) {
            return true;
        }
        
        foreach ($pObject->getClass()->getSuperclasses() as $superclass) {
            if ($superclass == $this->className) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param PConstraint $constraint
     */
    public function setConstraint(PConstraint $constraint) {
        $this->constraint = $constraint;
    }

}
