<?php

namespace ParrotDb\Persistence\Feather;

use \ParrotDb\Persistence\Deserializer;

/**
 * The FeatherClassDeserializer handles the deserialization of a
 * feather file into a PClass object
 *
 * @author J. Baum
 */
class FeatherClassDeserializer implements Deserializer {
    
    private $input;
    private $length;
    
    /**
     * @param string $input
     */
    public function setInput($input) {
        $this->input = $input;
        $this->length = strlen($input);
    }
    
    /**
     * @return \ParrotDb\ObjectModel\PClass
     */
    public function deserialize() {
        
        $pClass = $this->createPClass($this->getName());
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $pClass->addField($field);
        }
        
        $superclasses = $this->getSuperclasses();
        foreach ($superclasses as $sc) {
            $pClass->addSuperclass($sc);
        }
        
        return $pClass;
    }
    
    private function getName() {
        $nameEndPos = strpos($this->input,",");
        
        return substr($this->input, 3, $nameEndPos-4);
    }
    
    private function getFields() {
        
        $attrToken = "attr{";
        $attrPos = strpos($this->input,$attrToken);
        $attrStartPos = $attrPos+strlen($attrToken);
        $attrEndPos = strpos($this->input, "}")-$attrStartPos;
        $arr = explode(",", substr($this->input, $attrStartPos, $attrEndPos));
        
        $cleanArr = array();
        foreach ($arr as $element) {
            $cleanArr[] = substr($element,1,strlen($element)-2);
        }
        
        return $cleanArr;
    }
    
    private function getSuperclasses() {
        
        $scToken = "sc{";
        $scPos = strpos($this->input,$scToken);
        $scStartPos = $scPos+strlen($scToken);
        $scEndPos = strpos($this->input, "}", $scStartPos)-$scStartPos;
        $arr = explode(",", substr($this->input, $scStartPos, $scEndPos));
        
        $cleanArr = array();
        if ($scEndPos > 0) { 
            foreach ($arr as $element) {
                $cleanArr[] = substr($element,1,strlen($element)-2);
            }
        }
        
        
        return $cleanArr;
    }
    
    private function createPClass($name) {
        return new \ParrotDb\ObjectModel\PClass($name);
    }
    
}
