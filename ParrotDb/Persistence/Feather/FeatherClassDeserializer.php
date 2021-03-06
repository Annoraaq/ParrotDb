<?php

namespace ParrotDb\Persistence\Feather;

use \ParrotDb\Persistence\Deserializer;
use \ParrotDb\Utils\PUtils;

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
        $nameEndPos = mb_strpos($this->input,",");
        
        $offset = mb_strlen("c['");
        $length = $nameEndPos-$offset - mb_strlen("'");
        return mb_substr($this->input, $offset, $length);
    }
    
    private function getFields() {
        
        $attrToken = "attr{";
        $attrPos = mb_strpos($this->input,$attrToken);
        $attrStartPos = $attrPos+strlen($attrToken);
        $attrEndPos = mb_strpos($this->input, "}")-$attrStartPos;
        $arr = explode(",", mb_substr($this->input, $attrStartPos, $attrEndPos));
        
        $cleanArr = array();
        foreach ($arr as $element) {
            $cleanArr[] = PUtils::cutHeadAndTail($element);
        }
        
        return $cleanArr;
    }
    
    private function getSuperclasses() {
        
        $scToken = "sc{";
        $scPos = mb_strpos($this->input, $scToken);
        $scStartPos = $scPos + mb_strlen($scToken);
        $scEndPos = mb_strpos($this->input, "}", $scStartPos) - $scStartPos;
        $arr = explode(",", mb_substr($this->input, $scStartPos, $scEndPos));

        $cleanArr = array();
        if ($scEndPos > 0) {
            foreach ($arr as $element) {
                $cleanArr[] = PUtils::cutHeadAndTail($element);
            }
        }


        return $cleanArr;
    }
    
    private function createPClass($name) {
        return new \ParrotDb\ObjectModel\PClass($name);
    }
    
}
