<?php

namespace ParrotDb\Persistence\Feather;

use \ParrotDb\Persistence\Deserializer;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Utils\PXmlUtils;

/**
 * The XmlClassDeserializer handles the deserialization of an xml file into
 * a PObject object
 *
 * @author J. Baum
 */
class FeatherObjectDeserializer implements Deserializer {

    private $pClass;
    private $input;
    private $length;

    /**
     * @param PClass $pClass
     */
    public function __construct(PClass $pClass) {
        $this->pClass = $pClass;
    }
    
    /**
     * @param string $input
     */
    public function setInput($input) {
        $this->input = $input;
        $this->length = strlen($input);
    }
    
    private function getObjectId() {
        $idEndPos = strpos($this->input,",");
        
        return substr($this->input, 1, $idEndPos-1);
    }

    private function parseAttribute($offset) {
        
        // consider "," and "'"
        $offset += 2;
        $length = strpos($this->input,":", $offset) - $offset - 1;
        $name = substr($this->input, $offset, $length);

        // length
        
        // consider ":" two times
        $offset = $offset+$length+2;
        $lengthEndPos = strpos($this->input,":", $offset);
        $length = substr($this->input, $offset, $lengthEndPos-$offset);
        
        $value = substr($this->input, $lengthEndPos+1, $length+2);
//        
//        $attribute = new \ParrotDb\ObjectModel\PAttribute(
//            $name,
//            $this->parseAttributeValue($value)
//        );
        
        return array(
            'name' => $name,
            'offset' => $lengthEndPos+1+$length+2,
            'value' => $this->parseAttributeValue($value)
        );
         
    }
    
     private function parseAttributeInput($string, $offset) {
        
        // consider "," and "'"
        $offset += 3;
        $length = strpos($string,":", $offset) - $offset;
        $name = substr($string, $offset, $length-1);
        

        // length
        
        // consider ":" two times
        $offset = $offset+$length+1;
        $lengthEndPos = strpos($string,":", $offset);
        $length = substr($string, $offset, $lengthEndPos-$offset);
        
        $value = substr($string, $lengthEndPos+1, $length+2);
//        
//        $attribute = new \ParrotDb\ObjectModel\PAttribute(
//            $name,
//            $this->parseAttributeValue($value)
//        );
        
        return array(
            'name' => $name,
            'offset' => $lengthEndPos+1+$length+1,
            'value' => $this->parseAttributeValue($value)
        );
         
    }
    
    private function parseAttributeValue($value) {
        
        
        $cleaned = substr($value,1, strlen($value)-2);
        
        // string
        switch($value[0]) {
            case "'":
                if (strlen($value) <= 2) {
                   return ''; 
                }
                return $cleaned;
            case "(":
                if (strlen($value) <= 2) {
                    return null;
                }
                return new PObjectId($cleaned);
            case "{":
                if (strlen($value) <= 2) {
                    return array();
                }
               return $this->parseArray($value);
        }
        
       
        
        return $value;
    }
    
    /**
     * @return PObject
     */
    public function deserialize() {
        
        $pObject = new PObject(new PObjectId($this->getObjectId()));
        $pObject->setClass($this->pClass);
        
        $idEndPos = strpos($this->input,",");
        $lengthEndPos = strpos($this->input,",", $idEndPos+1);
        
        
        $offset = $lengthEndPos;
        while (($offset < ($this->length-2))) {

            $attribute = $this->parseAttribute($offset);

            $pObject->addAttribute($attribute['name'], $attribute['value']);
            $offset = $attribute['offset'];
        }
        
        
//        $fields = $this->getFields();
//        foreach ($fields as $field) {
//            $pClass->addField($field);
//        }
//        
//        $superclasses = $this->getSuperclasses();
//        foreach ($superclasses as $sc) {
//            $pClass->addSuperclass($sc);
//        }
        
        
//        $id = PXmlUtils::firstElemByTagName($this->object, "id")->nodeValue;
//        $pObject = new PObject(new PObjectId($id));
//        $pObject->setClass($this->pClass);
//
//
//        $attributes = PXmlUtils::firstElemByTagName($this->object, "attributes")
//         ->getElementsByTagName("attribute");
//
//        foreach ($attributes as $attribute) {
//            $valElem = PXmlUtils::firstElemByTagName($attribute, "value");
//            if (PXmlUtils::equalsFirstChildName($valElem, "objectId")) {
//                $value = new PObjectId($valElem->firstChild->nodeValue);
//            } else if (PXmlUtils::equalsFirstChildName($valElem, "array")) {
//                $value = $this->parseArray($valElem->firstChild);
//            } else {
//                $value = $valElem->nodeValue;
//            }
//
//            $pObject->addAttribute(
//             PXmlUtils::firstElemByTagName($attribute, "name")->nodeValue,
//             $value
//            );
//        }

        return $pObject;
    }
    
   

    private function parseArray($arrayString) {
        
        $arrayLen = strlen($arrayString);
        $offset = -1;
        $outArr = array();
        $limit = 0;
        
        while ($offset < ($arrayLen-2) && $limit < 50) {

            $attribute = $this->parseAttributeInput($arrayString, $offset);

            $outArr[$attribute['name']] = $attribute['value'];
            $offset = $attribute['offset'];
            $limit++;
        }
        
        return $outArr;
    }

}
