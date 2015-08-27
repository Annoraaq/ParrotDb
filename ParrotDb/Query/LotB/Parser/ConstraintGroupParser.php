<?php

namespace ParrotDb\Query\LotB\Parser;


use \ParrotDb\Query\Constraint\POrConstraint;
use \ParrotDb\Query\Constraint\PAndConstraint;

/**
 * Description of ConstraintGroupParser
 *
 * @author J. Baum
 */
abstract class ConstraintGroupParser {
    
    /**
     * @var Parser 
     */
    protected $mainParser;
    
    /**
     * @var array
     */
    protected $exploded;
    
    /**
     * @var boolean
     */
    protected $hasGrouping;
    
    
    /**
     * @var string
     */
    protected $grouping;
    
    /**
     * @var array
     */
    protected $constraints;
    
    public function __construct(Parser $mainParser) {
        $this->mainParser = $mainParser;
    }
    
    /**
     * Parses a query starting with an opening parathesis.
     * 
     * @param array $exploded
     * @return POrConstraint|PEmptyConstraint|PAndConstraint
     * @throws PException
     */
    public function parse($exploded) {
        
       $this->exploded = $exploded;
       

        
        if ($this->isEmptyStatement()) {
            return new PEmptyConstraint();
        }
        
        $this->checkGrouping();

        if (!$this->hasGrouping) {
            $this->checkValidity();
            return $this->noGroupingConstraint();
        } else if ($this->grouping == Parser::TOKEN_AND) {
            return new PAndConstraint($this->constraints);           
        } else if ($this->grouping == Parser::TOKEN_OR) {
            return new POrConstraint($this->constraints);
        }
        
    }
    
    /**
     * Specifies the baehaviour if no grouping was detected.
     */
    protected abstract function noGroupingConstraint();
    
    /**
     * Checks if the Constraint is a grouped one.
     */
    private function checkGrouping() {
        $this->grouping = $this->getGrouping();
        if ($this->grouping != null) {
            $this->hasGrouping = true;
            $this->collectGroupingConstraints();
        } else {
            $this->hasGrouping = false;
        }
    }
    
    /**
     * Collects all the single constraints grouped by "," or "or".
     */
    private function collectGroupingConstraints() {
        $this->constraints = [];
        $grouping = $this->splitGroup();
        foreach ($grouping as $group) {
            $this->constraints[] = $this->mainParser->parseExploded($group);
        }
    }

    /**
     * Checks the validity of the group statement.
     * 
     * @throws PException
     */
    private function checkValidity() {
         if (!isset($this->exploded[1])) {
            throw new PException("LotB Syntax Error: Found no closing paranthesis.");
        }
    }
    
    /**
     * Checks if group statement is empty: ()
     * 
     * @return boolean
     */
    private function isEmptyStatement() {
        return ($this->exploded[1] == Parser::TOKEN_PC);
    }
    
    /**
     * Checks, whether the given string is a group of constraints and gives
     * back the group connector or null.
     * 
     * @return mixed "," | "or" | null
     * @throws PException
     */
    private function getGrouping() {
        $openParantheses = 0;
       
        $and = false;
        
        foreach ($this->exploded as $elem) {
            if ($elem == Parser::TOKEN_PO) {
                $openParantheses++;
            } else if ($elem == Parser::TOKEN_PC) {
                if ($openParantheses == 0) {
                    throw new PException("Invalid LotB query.");
                } else {
                    $openParantheses--;
                }
            } else if ($elem == Parser::TOKEN_AND && $openParantheses == 0) {
                $and = true;
            } else if ($elem == Parser::TOKEN_OR && $openParantheses == 0) {
                return Parser::TOKEN_OR;
            }
        }
        
        if ($and) {
            return Parser::TOKEN_AND;
        }
        
        return null;
    }
    
    /**
     * Splits a group of constraints at their connectors ("or" or ",").
     * 
     * @return array
     */
    public function splitGroup() {
        $stack = [];
        $openParantheses = 0;
        
        $groups = [];
        foreach ($this->exploded as $elem) {
            if ($elem == Parser::TOKEN_PO) {
                $stack[] = $elem;
                $openParantheses++;
            } else if ($elem == Parser::TOKEN_PC) {
                $stack[] = $elem;
                $openParantheses--;
            } else if ($elem == $this->grouping) {
                if ($openParantheses == 0) {
                    $groups[] = $stack;
                    $stack = [];
                } else {
                    $stack[] = $elem;
                }
            } else {
                $stack[] = $elem;
            }
   
        }
        $groups[] = $stack;

        return $groups;
    }
    
}
