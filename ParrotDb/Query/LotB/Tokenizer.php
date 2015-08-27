<?php

namespace ParrotDb\Query\LotB;



/**
 * Description of Tokenizer
 *
 * @author J. Baum
 */
class Tokenizer {
    
    const TOKEN_PO = "(";
    const TOKEN_PC = ")";
    const TOKEN_AND = ",";
    const TOKEN_EQ = "=";
    const TOKEN_QM = '"';
    const TOKEN_LTE = '<=';
    const TOKEN_GTE = '>=';
    const TOKEN_LT = '<';
    const TOKEN_GT = '>';
    const TOKEN_CBO = '{';
    const TOKEN_CBC = '}';
    const TOKEN_ASC = '<<';
    const TOKEN_DESC = '>>';
    
    /**
     *
     * @var string Input string
     */
    protected $input;
    
    /**
     *
     * @var int potential start of new word
     */
    protected $start;
    
    /**
     *
     * @var array Output array
     */
    protected $out;
    
    /**
     *
     * @var int Current position in input string
     */
    protected $i;
    
    /**
     *
     * @var boolean States whether tokenizer is in "quotation mark mode".
     */
    protected $inQm;
    
    /**
     *
     * @var int Length of input string
     */
    protected $lenght;
  
    /**
     * 
     * @param string $input Input String
     */
    public function __construct($input) {
        $this->input = $input;
    }
    
    /**
     * Divides string into array of tokens and words.
     * 
     * @return array 
     */
    public function tokenize() {
       
        $this->init();
        for ($this->i=1; $this->i<$this->length; $this->i++) {
            if ($this->isClosingQm()) {
                $this->processClosingQm();
            } else if ($this->isInQuotationMode()) { 
                continue;
            } else if ($this->isStandardSingleton ()) {
                $this->processStandardSingleton();
            } else if ($this->isSingleton(self::TOKEN_LT)) {
                $this->processDouble(self::TOKEN_LTE, self::TOKEN_EQ, self::TOKEN_LT, self::TOKEN_ASC);
            } else if ($this->isSingleton(self::TOKEN_GT)) {  
                $this->processDouble(self::TOKEN_GTE, self::TOKEN_EQ, self::TOKEN_GT, self::TOKEN_DESC);
            } else if ($this->isSingleton(self::TOKEN_EQ)) {
                $this->processSingleton(self::TOKEN_EQ);
            } else if ($this->isSingleton(self::TOKEN_QM)) {
                $this->shiftStart();
                $this->activateQuotationMode();
            } else if ($this->isNewWord()) {
                $this->addToken();
                $this->shiftStart();
            } else if ($this->isLastChar()) {
                if ($this->input[$this->i-1] == " ") {
                    $this->out[] = $this->input[$this->i];
                } else {
                    $this->out[] = substr($this->input, $this->start);
                }
     
            } else if ($this->isJustChar()) {
                $this->shiftStart();
            } 
        }
        
        return $this->out;
    }
    
    /**
     * Initializes tokenizer.
     */
    private function init() {
        $this->input = trim($this->input);
        $this->length = strlen($this->input);
        $this->out = [];
        $this->start = 0;
        $this->deactivateQuotationMode();
    }
    
    /**
     * Checks, whether tokenizer is in quotation mode.
     * 
     * @return boolean
     */
    private function isInQuotationMode() {
        return $this->isQm;
    }
    
    /**
     * Activates quotation mode.
     */
    private function activateQuotationMode() {
        $this->isQm = true;
    }
    
    /**
     * Deactivates quotation mode.
     */
    private function deactivateQuotationMode() {
        $this->isQm = false;
    }
    
    /**
     * Checks if current char is last char.
     * 
     * @return boolean
     */
    private function isLastChar() {
        return ($this->i == $this->length-1);
    }
    
    /**
     * Checks, whether last character is a space.
     * 
     * @return boolean
     */
    private function isJustChar() {
        return ($this->input[$this->i-1] == " ");
    }
    
    /**
     * Checks, whether current char is a closing quotation mark.
     * 
     * @return boolean
     */
    private function isClosingQm() {
        return ($this->isInQuotationMode() && $this->isSingleton(self::TOKEN_QM));
    }
    
    /**
     * Checks, whether current char is a standard singleton token.
     * 
     * @return boolean
     */
    private function isStandardSingleton() {
        if ($this->isSingleton(self::TOKEN_PO)
            || $this->isSingleton(self::TOKEN_PC)
            || $this->isSingleton(self::TOKEN_AND)
            || $this->isSingleton(self::TOKEN_CBO)
            || $this->isSingleton(self::TOKEN_CBC)) {
            return true;
        }
        
        return false;
    }

    /**
     * Shifts start position.
     */
    private function shiftStart() {
        $this->start = $this->i;
    }
    
    /**
     * Checks, whether current char is the beginning of a new word.
     * 
     * @return boolean
     */
    private function isNewWord() {
        return (($this->input[$this->i] == " ") && ($this->input[$this->i-1] != " "));
    }
    
    /**
     * Checks, whether current char equals the given singleton token.
     * 
     * @return boolean
     */
    private function isSingleton($singleton) {
        return ($this->input[$this->i] == $singleton);
    }
    
    /**
     * Processes the current char as a standard singleton.
     */
    private function processStandardSingleton() {
        $this->processSingleton($this->input[$this->i]);
    }
    
    /**
     * Processes the current char as the given singleton.
     * 
     * @param string $token
     */
    private function processSingleton($token) {
        $this->addToken();
        $this->out[] = $token;
        $this->start = $this->i+1;
    }
    
    /**
     * Processes a double token.
     * 
     * @param string $token
     * @param string $token2
     * @param string $token3
     * @param string $token4
     */
    private function processDouble($token, $token2, $token3, $token4) {
        $this->addToken();
        if ($this->input[$this->i + 1] == $token2) {
            $this->out[] = $token;
            $this->start = $this->i + 2;
            $this->i++;
        } else if ($this->input[$this->i + 1] == $token3) {
            $this->out[] = $token4;
            $this->start = $this->i + 2;
            $this->i++;
        } else {
            $this->out[] = $token3;
            $this->start = $this->i + 1;
        }
    }
    
    /**
     * Processes a closing quotation mark.
     */
    private function processClosingQm() {
        $token = substr($this->input, $this->start, ($this->i - $this->start + 1));
        $this->deactivateQuotationMode();

        if ($this->isValid($token)) {
            $this->out[] = $token;
        }

        $this->start = $this->i + 1;
    }

    /**
     * Checks, whether the given token is not empty.
     * 
     * @param string $token
     * @return boolean
     */
    private function isValid($token) {
        return (strlen(trim($token)) > 0);
    }
    
    /**
     * Adds current token to output array.
     */
    private function addToken() {
        $token = substr($this->input, $this->start, ($this->i - $this->start));
        if ($this->isValid($token)) {
            $this->out[] = $token;
        }
    }
}
