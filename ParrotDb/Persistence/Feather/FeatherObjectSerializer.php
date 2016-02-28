<?php


namespace ParrotDb\Persistence\Feather;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\ObjectModel\PObjectId;

/**
 * Feather-serializer for objects of PObject
 *
 * @author J. Baum
 */
class FeatherObjectSerializer {
    
    
    /**
     * @var PObject PObject to serialize.
     */
    protected $pObject;
    
    /**
     * @param PObject $pObject
     */
    public function setPObject(PObject $pObject) {
        $this->pObject = $pObject;
    }

    /**
     * @return string
     */
    public function serialize() {
        $output = '[' . $this->pObject->getObjectId()->getId() . ',';
        $attributes = $this->createAttributes();
        $output .= mb_strlen($attributes) . ',' . $attributes . ']';
        
        return $output;
    }
    
    
    private function createAttributes() {
        $output = '';

        $empty = true;
        foreach ($this->pObject->getAttributes() as $attr) {
            $attribute = $this->createAttributeElement($attr);
            $output .= $attribute . ",";
            $empty = false;
        }
        
        if (!$empty) {
            $output = PUtils::cutLastChar($output);
        }

        return $output;
    }
    
    private function createAttributeElement($attr) {
        
        $output = "'" . $attr->getName() . "':";
        
        $attrVal = $this->getAttrValue($attr);
        
        $output .= (mb_strlen($attrVal)-2) . ":" . $attrVal;
        
        return $output;
    }
    
    private function getAttrValue($attr) {
        if (PUtils::isArray($attr->getValue())) {
            return $this->processArray($attr->getValue());
        } else if ($attr->getValue() instanceof PObjectId) {
            return $this->processObjectId($attr->getValue());
        } else {
            return "'" . $attr->getValue() . "'";
        }

    }

    private function processArray($attr) {
        $output = '{';
        
        $empty = true;
        foreach ($attr as $key => $val) {
            $output .= "'" . $key . "':";
            $valEl = $this->createValueElement($val);
            $output .= (mb_strlen($valEl)-2) . ":" . $valEl . ",";
            $empty = false;
        }
        
        if (!$empty) {
            $output = PUtils::cutLastChar($output);
        }
        
        $output .= '}';
        
        return $output;
    }
    
    private function createValueElement($val) {
        if (PUtils::isArray($val)) {
            return $this->processArray($val);
        } else if ($val instanceof PObjectId) {
            return $this->processObjectId($val);
        } else {
            return "'" . $val . "'";
        }
    }
    

    private function processObjectId($pObjectId) {
        return "(" . $pObjectId->getId() . ")";
    }
    
}
