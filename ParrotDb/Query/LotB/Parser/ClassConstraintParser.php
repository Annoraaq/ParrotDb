<?php

namespace ParrotDb\Query\LotB\Parser;

use \ParrotDb\Utils\PUtils;
use \ParrotDb\Query\Constraint\PClassConstraint;

/**
 * Description of ClassConstraintParser
 *
 * @author J. Baum
 */
class ClassConstraintParser {
    
    /**
     * @var array Input
     */
    protected $exploded;
    
    /**
     * @var Parser 
     */
    protected $mainParser;
    
    /**
     * @var boolean
     */
    protected $hasConstraint;
    
    /**
     * @var boolean
     */
    protected $hasAmountLimitation;
    
    /**
     * @var int
     */
    protected $length;
    
    /**
     * @var string
     */
    protected $className;
    
    /**
     * @var int
     */
    protected $amount;
    
    /**
     * @var boolean
     */
    protected $hasOrdering;
    
    /**
     * @var string
     */
    protected $order;
    
    /**
     * @var PConstraint
     */
    protected $constraint;
    
    /**
     * @var array
     */
    protected $orderAttributes;
    
    /**
     * @param Parser $mainParser
     */
    public function __construct(Parser $mainParser) {
        $this->mainParser = $mainParser;
    }
    
    /**
     * @param array $exploded
     * @return PConstraint
     */
    public function parse($exploded) {
        $this->exploded = $exploded;
        $this->checkLength();
        $this->checkAmountLimitation();
        $this->checkOrdering();
        $this->constraint = new PClassConstraint($this->className);

        if ($this->hasOrdering) {
            $this->setOrder();
        }

        if ($this->hasAmountLimitation) {
            $this->constraint->setAmount($this->amount);
        }

        $this->constraint->setConstraint(
         $this->mainParser->parseExploded(
          PUtils::cutArrayFront(
           $exploded, $this->cutLen
          )
         )
        );

        return $this->constraint;
    }
    
    /**
     * Sets the input lengths and checks if it is high enough.
     * 
     * @throws PException
     */
    private function checkLength() {
        $this->length = count($this->exploded);
        if ($this->length < 2) {
            throw new PException("Invalid LotB query.");
        }
    }
    
    /**
     * Checks, whether the class constraint has an amount limitation.
     */
    private function checkAmountLimitation() {
        $this->hasAmountLimitation = PUtils::isNumber($this->exploded[1]);
        if ($this->hasAmountLimitation) {
            $this->amount = $this->exploded[1];
            $this->className = $this->exploded[2];
            $this->cutLen = 3;
        } else {
            $this->className = $this->exploded[1];
            $this->cutLen = 2;
        }
    }
    
    /**
     * Checks, whether the class constraint has an ordering.
     */
    private function checkOrdering() {  
        
        $potentialOrder = null;
        
        if ($this->hasAmountLimitation) {
            if (isset($this->exploded[3])) {
                $potentialOrder = $this->exploded[3];
            }
        } else {
            if (isset($this->exploded[2])) {
                $potentialOrder = $this->exploded[2];
            }
        }
        
        if ($potentialOrder == Parser::TOKEN_DESC) {
            $this->hasOrdering = true;
            $this->order = Parser::TOKEN_DESC;
        } else if ($potentialOrder == Parser::TOKEN_ASC) {
            $this->hasOrdering = true;
            $this->order = Parser::TOKEN_ASC;
        } else {
            $this->hasOrdering = false;
        }
    }
    
    
    
    
    /**
     * Sets the order settings of the constraint.
     */
    private function setOrder() {
        $this->constraint->setOrder($this->order);
        $this->orderAttributes = [];
        
        if ($this->hasAmountLimitation) {
            $startIndex = 4;
        } else {
            $startIndex = 3;
        }
        
        $this->cutLen = $startIndex+1;
        if ($this->exploded[$startIndex] == Parser::TOKEN_PO) {
            $this->processMultipleOrderings($startIndex);
        } else {
            $this->processSingleOrdering($startIndex);
        }

        $this->constraint->setOrderAttributes($this->orderAttributes);
    }
    
    /**
     * Parses the multiple ordering attributes and adds them to the constraint.
     * 
     * @param int $startIndex
     */
    private function processMultipleOrderings($startIndex) {
        $i = $startIndex;
        while (true) {
            if (!isset($this->exploded[$i])) {
                break;
            }

            if ($this->exploded[$i] == Parser::TOKEN_PC) {
                break;
            }

            if ($this->exploded[$i] != Parser::TOKEN_PO &&
                $this->exploded[$i] != Parser::TOKEN_AND) {
                $this->orderAttributes[] = $this->exploded[$i];
            }

            $i++;
        }

        $this->cutLen = $startIndex + 2 + $i;
    }
    
    /**
     * Parses a single ordering attribute and adds it to the constraint.
     * 
     * @param int $startIndex
     */
    private function processSingleOrdering($startIndex) {
        $this->orderAttributes[] = $this->exploded[$startIndex];
    }
}
