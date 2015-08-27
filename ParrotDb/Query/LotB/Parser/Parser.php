<?php

namespace ParrotDb\Query\LotB\Parser;

use \ParrotDb\Core\PException;
use \ParrotDb\Persistence\Database;
use \ParrotDb\Query\Constraint\PEmptyConstraint;
use \ParrotDb\Query\LotB\Tokenizer;

/**
 * Description of Parser
 *
 * @author J. Baum
 */
class Parser {

    /**
     * @var Tokenizer
     */
    protected $tokenizer;
    
    const TOKEN_GET = "get";
    const TOKEN_PO = "(";
    const TOKEN_PC = ")";
    const TOKEN_AND = ",";
    const TOKEN_OR = "or";
    const TOKEN_EQ = "=";
    const TOKEN_QM = '"';
    const TOKEN_LTE = '<=';
    const TOKEN_GTE = '>=';
    const TOKEN_LT = '<';
    const TOKEN_GT = '>';
    const TOKEN_CBO = '{';
    const TOKEN_CBC = '}';
    const TOKEN_CONTAINS = 'contains';
    const TOKEN_NOT = 'not';
    const TOKEN_ASC = '<<';
    const TOKEN_DESC = '>>';
    
    /**
     *
     * @var array Datastructure used for parsing 
     */
    protected $stack;
    
    /**
     * @var Database
     */
    protected $database;

    
    public function __construct(Database $database) {
        $this->database = $database;
    }
    
    /**
     * Parses an input string into a constraint.
     * 
     * @param string $string
     * @return PConstraint
     * @throws PException
     */
    public function parse($string) {
        $this->tokenizer = new Tokenizer($string);
        $exploded = $this->tokenizer->tokenize();
        $length = count($exploded);
      
        if ($length <= 0 || $exploded[0] != "get") {
            throw new PException("Invalid LotB query.");
        }

        return $this->parseExploded($exploded);
    }
    
    /**
     * @return Database
     */
    public function getDatabase() {
        return $this->database;
    }
    
    /**
     * Parses a given array of tokens and returns a constraint.
     * 
     * @param array $exploded
     * @return PConstraint
     */
    public function parseExploded($exploded) {

        if (!isset($exploded[0])) {
            return new PEmptyConstraint();
        }

        switch ($exploded[0]) {
            case (self::TOKEN_GET):
                return (new ClassConstraintParser($this))->parse($exploded);
            case (self::TOKEN_PO):
                return (new ParanthesisParser($this))->parse($exploded);
            case (self::TOKEN_NOT):
                return (new NotConstraintParser($this))->parse($exploded);
            default:
                return (new SingletonParser($this))->parse($exploded);

        }

        return new PEmptyConstraint();
    }

}
