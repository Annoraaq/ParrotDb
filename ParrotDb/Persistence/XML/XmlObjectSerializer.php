<?php


namespace ParrotDb\Persistence\Xml;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\ObjectModel\PObjectId;

/**
 * XML-serializer for objects of PObject
 *
 * @author J. Baum
 */
class XmlObjectSerializer {
    
    /**
     * @var \DOMDocument DOMDocument to serialize into.
     */
    protected $domDocument;
    
    /**
     * @var PObject PObject to serialize.
     */
    protected $pObject;
    
    
    /**
     * @param \DOMDocument $domDocument DOMDocument to serialize into.
     */
    public function __construct(\DOMDocument $domDocument = null) {
        if ($domDocument == null) {
            $this->domDocument = new \DOMDocument;
        }
    }
    
    public function setPObject(PObject $pObject) {
        $this->pObject = $pObject;
    }
    
    /**
     * @param \DOMDocument $domDocument DOMDocument to serialize into.
     */
    public function setDomDocument(\DOMDocument $domDocument) {
         $this->domDocument = $domDocument;
    }
    
    public function getDomDocument() {
        return $this->domDocument;
    }
    
    public function serialize() {
        $object = $this->domDocument->createElement("object");

        $object->appendChild($this->createIdElement());
        $object->appendChild($this->createAttributesElement());
        
        return $object;
    }
    
    private function createIdElement() {
        return $this->domDocument->createElement(
            "id",
            $this->pObject->getObjectId()->getId()
        );
    }
    
    private function createAttributesElement() {
        $attributes = $this->domDocument->createElement("attributes");

        foreach ($this->pObject->getAttributes() as $attr) {
            $attributes->appendChild($this->createAttributeElement($attr));
        }

        return $attributes;
    }
    
    private function createAttributeElement($attr) {
        $attrElem = $this->domDocument->createElement("attribute");
        $attrElem->appendChild(
         $this->domDocument->createElement(
          "name", $attr->getName()
         )
        );

        

        $attrVal = $this->setAttrValue(
         $attr, $this->domDocument->createElement("value")
        );

        $attrElem->appendChild($attrVal);
        
        
        return $attrElem;
    }
    
    private function setAttrValue($attr, $attrVal) {
        if (PUtils::isArray($attr->getValue())) {
            $attrVal->appendChild($this->processArray($attr->getValue()));
        } else if ($attr->getValue() instanceof PObjectId) {
            $attrVal->appendChild($this->processObjectId($attr->getValue()));
        } else {
            $attrVal = $this->domDocument->createElement(
             "value", $attr->getValue()
            );
        }

        return $attrVal;
    }
    
    /**
     * Serializes a given array into XML and returns a \DOMElement.
     * 
     * @param array $attr
     * @return \DOMElement
     */
    private function processArray($attr) {
        $arrayElement = $this->domDocument->createElement("array");
        foreach ($attr as $key => $val) {
            $elem = $this->domDocument->createElement("elem");
            $elem->appendChild($this->domDocument->createElement("key", $key));
            $elem->appendChild($this->createValueElement($val));
            $arrayElement->appendChild($elem);
        }
        
        return $arrayElement;
    }
    
    private function createValueElement($val) {
        if (PUtils::isArray($val)) {
            $value = $this->domDocument->createElement("value");
            $value->appendChild($this->processArray($val));
        } else if ($val instanceof PObjectId) {
            $value = $this->domDocument->createElement("value");
            $value->appendChild($this->processObjectId($val));
        } else {
            $value = $this->domDocument->createElement("value", $val);
        }
        
        return $value;
    }
    
    /**
     * Serializes a given PObjectId into XML and returns a \DOMElement.
     * 
     * @param PObjectId $pObjectId
     * @return \DOMElement
     */
    private function processObjectId($pObjectId) {
        return $this->domDocument->createElement("objectId", $pObjectId->getId());
    }
    
}
