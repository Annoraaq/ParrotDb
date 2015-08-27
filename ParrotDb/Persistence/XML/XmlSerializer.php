<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Persistence\Serializer;
use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Utils\PUtils;

/**
 * Description of XmlSerializer
 *
 * @author J. Baum
 */
class XmlSerializer implements Serializer {
    
    public function serialize(PObject $pObject) {
        $xml = new \DOMDocument();
        $object = $xml->createElement( "object" );
        $object->appendChild($xml->createElement("id", $pObject->getObjectId()->getId()));
     
        $attributes = $xml->createElement("attributes");
        
        foreach ($pObject->getAttributes() as $attr) {
            if ($attr->getName() == "partner") {
                echo getType($attr->getValue());
            }
            if (PUtils::isArray($attr->getValue())) {
                $attrElem = $xml->createElement("attribute");
                $attrElem->appendChild($xml->createElement("name", $attr->getName()));
                $attrVal = $xml->createElement("value");
                $attrElem->appendChild($attrVal);
                $arrElem = $xml->createElement("array");
                $arrElem->appendChild($this->processArray($xml, $attr->getValue()));
                $attrVal->appendChild($arrElem);
                $attributes->appendChild($attrElem);
              // $attributes->appendChild($xml->createElement($attr->getName(), $this->processArray($xml, $attr->getValue()))); 
            } else if ($attr->getValue() instanceof PObjectId) {
                $attrElem = $xml->createElement("attribute");
                $attrElem->appendChild($xml->createElement("name", $attr->getName()));
                $attrVal = $xml->createElement("value");
                $attrElem->appendChild($attrVal);
                $idElem = $xml->createElement("id");
                $idElem->appendChild($this->processObjectId($xml, $attr->getValue()));
                $attrVal->appendChild($idElem);
                $attributes->appendChild($attrElem);
               //$attributes->appendChild($xml->createElement($attr->getName(), $this->processObjectId($xml, $attr->getValue()))); 
            } else {
               $attributes->appendChild($xml->createElement($attr->getName(), $attr->getValue())); 
            }
            
        }
           $object->appendChild($attributes);
        
//        $fields = $xml->createElement("fields");
//        $class->appendChild($fields);
//        foreach ($pClass->getFields() as $field) {
//            $fields->appendChild($xml->createElement("field", $field));
//        }
//        
//        $superclasses = $xml->createElement("superclasses");
//        $class->appendChild($superclasses);
//        foreach ($pClass->getSuperclasses() as $superclass) {
//            $superclasses->appendChild($xml->createElement("superclass", $superclass));
//        }
//        
        $xml->appendChild($object);
        
        return $xml->saveXML();
    }
    
    private function processArray($xml, $attr) {
        $arrayElement = $xml->createElement("array");
        foreach ($attr as $key => $val) {
            $elem = $xml->createElement("elem");
            $elem->appendChild($xml->createElement("key", $key));
           
           
            if (PUtils::isArray($val)) {
                $value = $xml->createElement("value");
                $value->appendChild($this->processArray($xml, $val));
                $elem->appendChild($value);
            } else if ($val instanceof PObjectId) {
                $value = $xml->createElement("value");
                $value->appendChild($this->processObjectId($xml, $val));
                $elem->appendChild($value);
            } else {
                if (PUtils::isObject($val)) {
                    var_dump($val);
                }
                $elem->appendChild($xml->createElement("value", $val));
            }
            
            $arrayElement->appendChild($elem);
        }
        
        return $arrayElement;
    }
    
    private function processObjectId($xml, $attr) {
        return $xml->createElement("objectId", $attr->getId());
    }
    
    public function serializeClass(PClass $pClass) {
        $xml = new \DOMDocument();
        $class = $xml->createElement( "class" );
        $class->appendChild($xml->createElement("name", $pClass->getName()));
        
        $fields = $xml->createElement("fields");
        $class->appendChild($fields);
        foreach ($pClass->getFields() as $field) {
            $fields->appendChild($xml->createElement("field", $field));
        }
        
        $superclasses = $xml->createElement("superclasses");
        $class->appendChild($superclasses);
        foreach ($pClass->getSuperclasses() as $superclass) {
            $superclasses->appendChild($xml->createElement("superclass", $superclass));
        }
        
        $xml->appendChild($class);
        
        return $xml->saveXML();
    }

}
