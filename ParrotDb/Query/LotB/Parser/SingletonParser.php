<?php

namespace ParrotDb\Query\LotB\Parser;

use \ParrotDb\Query\LotB\Parser\ConstraintGroupParser;
use \ParrotDb\Query\Constraint\PAttributeConstraint;
use \ParrotDb\Query\Constraint\PRelationConstraint;
use \ParrotDb\Utils\PUtils;

/**
 * Description of SingletonParser
 *
 * @author J. Baum
 */
class SingletonParser extends ConstraintGroupParser {
    
    /**
     * @var string 
     */
    protected $attrName;
    
    /**
     * @var string
     */
    protected $attrValue;
    
    /**
     * @var string
     */
    protected $attrOperator;
    
    /**
     * @var string
     */
    protected $className;
    
    /**
     * @var int
     */
    protected $amountOperator;
    
    /**
     * @var int
     */
    protected $amountOperand;
    
    /**
     * @var int
     */
    protected $cutLength;

    /**
     * @var boolean
     */
    protected $hasRelShortForm;
    
    /**
     * @inheritDoc
     */
    protected function noGroupingConstraint() {
        $this->checkConstraint();
        if ($this->isRelationConstraint()) {
            return $this->createRelConstr($this->exploded);
        } else {
            return $this->createAttrConstr();
        }
    }
    
    /**
     * Checks whether the current constraint is a relation constraint
     * or an attribute constraint.
     * 
     * @return boolean
     */
    private function isRelationConstraint() {
        return ($this->exploded[1] == Parser::TOKEN_CONTAINS);
    }
    
    /**
     * Checks whether the current relation constraint is in short form.
     * 
     * @return boolean
     */
    private function isRelShortForm() {
        return ($this->exploded[2] == Parser::TOKEN_CBO);
    }
    
    /**
     * Gets all the parameters of the constraint.
     */
    private function checkConstraint() {
        if ($this->isRelationConstraint()) {
            $this->attrName = $this->exploded[0];
            if ($this->isRelShortForm()) {
                $this->className = $this->exploded[3];
                $this->amountOperator = PRelationConstraint::OP_GTE;
                $this->amountOperand = 1;
                $this->cutLength = 4;
            } else {
                $this->className = $this->exploded[5];
                $this->amountOperator = $this->exploded[2];
                $this->amountOperand = $this->exploded[3];
                $this->cutLength = 6;
            }
        } else {
            $this->attrName = $this->exploded[0];
            $this->attrValue = $this->removeQm($this->exploded[2]);
            $this->attrOperator = $this->exploded[1];
        }
    }
    
    /**
     * Creates a new attribute constraint.
     * 
     * @param array $exploded
     * @return PAttributeConstraint
     */
    public function createAttrConstr() {
        return new PAttributeConstraint(
            $this->attrName,
            $this->attrValue,
            $this->attrOperator
        );
    }
    
    /**
     * Removes quotation marks of a string if it is wrapped in them.
     * 
     * @param string $string
     * @return string
     */
    private function removeQm($string) {
        if ($string[0] == '"') {
            return substr($string,1,strlen($string)-2);
        }
        
        return $string;
    }
    
    /**
     * Creates a new relation constraint.
     * 
     * @param array $exploded
     * @return PRelationConstraint
     */
    public function createRelConstr() {

        $this->exploded = PUtils::cutArrayTail(
            PUtils::cutArrayFront(
                $this->exploded,
                $this->cutLength
            )
        );

        return new PRelationConstraint(
            $this->mainParser->getDatabase(),
            $this->attrName,
            $this->className,
            $this->amountOperator,
            $this->amountOperand,
            $this->mainParser->parseExploded($this->exploded)
        );
    }

}
