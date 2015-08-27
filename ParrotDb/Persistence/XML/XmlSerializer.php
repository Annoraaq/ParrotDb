<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Persistence\Serializer;
use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Utils\PUtils;

/**
 * The XmlSerializer handles the serialization of PObject and PClass into
 * an xml string.
 *
 * @author J. Baum
 */
class XmlSerializer implements Serializer {
    
    protected $domDocument;
    
    public function __construct() {
        $this->domDocument = new \DOMDocument;
    }
    
    public function setDomDocument(\DOMDocument $domDocument) {
         $this->domDocument = $domDocument;
    }
    
    /**
     * Serializes the given PObject into XML
     * 
     * @param PObject $pObject PObject to serialize
     * @param \DOMDocument $xml DOMDocument to serialize the object into
     * @param \DOMElement $elem DOMElement in given DOMDocument to append the
     * serialized object as a child
     * @return \DOMDocument
     */
    public function serialize(PObject $pObject) {


        $object = $this->domDocument->createElement("object");

        $object->appendChild($this->domDocument->createElement("id",
          $pObject->getObjectId()->getId()));

        $attributes = $this->domDocument->createElement("attributes");

        foreach ($pObject->getAttributes() as $attr) {

            if (PUtils::isArray($attr->getValue())) {
                $attrElem = $this->domDocument->createElement("attribute");
                $attrElem->appendChild($this->domDocument->createElement("name",
                  $attr->getName()));
                $attrVal = $this->domDocument->createElement("value");
                $attrElem->appendChild($attrVal);
                $arrElem = $this->domDocument->createElement("array");
                $arrElem->appendChild($this->processArray($this->domDocument,
                  $attr->getValue()));
                $attrVal->appendChild($arrElem);
                $attributes->appendChild($attrElem);
            } else if ($attr->getValue() instanceof PObjectId) {
                $attrElem = $this->domDocument->createElement("attribute");
                $attrElem->appendChild($this->domDocument->createElement("name",
                  $attr->getName()));
                $attrVal = $this->domDocument->createElement("value");
                $attrElem->appendChild($attrVal);
                $idElem = $this->domDocument->createElement("id");
                $idElem->appendChild($this->processObjectId($this->domDocument,
                  $attr->getValue()));
                $attrVal->appendChild($idElem);
                $attributes->appendChild($attrElem);
            } else {
                $attributes->appendChild($this->domDocument->createElement($attr->getName(),
                  $attr->getValue()));
            }
        }
        $object->appendChild($attributes);
//
//        if ($elem != null) {
//            $elem->appendChild($object);
//        } else {
//            $xml->appendChild($object);
//        }

        return $object;
    }
    
    /**
     * Serializes a given array into XML and returns a \DOMElement.
     * 
     * @param \DOMDocument $xml
     * @param array $attr
     * @return \DOMElement
     */
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
    
    /**
     * Serializes a given PObjectId into XML and returns a \DOMElement.
     * 
     * @param \DOMDocument $xml
     * @param PObjectId $pObjectId
     * @return \DOMElement
     */
    private function processObjectId($xml, $pObjectId) {
        return $xml->createElement("objectId", $pObjectId->getId());
    }
    
    /**
     * Serializes a PClass as XML.
     * 
     * @param PClass $pClass
     * @param \DOMDocument $xml
     * @return \DOMDocument
     */
    public function serializeClass(PClass $pClass, $xml) {
        
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
        
  
        return $class;
    } 

}
