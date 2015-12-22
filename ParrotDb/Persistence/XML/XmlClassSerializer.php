<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\ObjectModel\PClass;

/**
 * XML-serializer for objects of PClass.
 *
 * @author J. Baum
 */
class XmlClassSerializer {
    
    /**
     * @var \DOMDocument DOMDocument to serialize into.
     */
    protected $domDocument;
    
    /**
     *
     * @var PClass PClass object to serialize.
     */
    protected $pClass;
    
    /**
     * @param \DOMDocument $domDocument DOMDocument to serialize into.
     */
    public function __construct(\DOMDocument $domDocument = null) {
        if ($domDocument == null) {
            $this->domDocument = new \DOMDocument;
        }
    }
    
    /**
     * @param \DOMDocument $domDocument DOMDocument to serialize into.
     */
    public function setDomDocument(\DOMDocument $domDocument) {
         $this->domDocument = $domDocument;
    }
    
    /**
     * @param PClass $pClass PClass object to serialize.
     */
    public function setPClass(PClass $pClass) {
        $this->pClass = $pClass;
    }
    
    /**
     * Serializes a PClass as XML.
     * 
     * @return \DOMDocument
     */
    public function serialize() {
        $class = $this->domDocument->createElement( "class" );
        $class->appendChild(
            $this->domDocument->createElement("name", $this->pClass->getName())
        );
        $class->appendChild($this->serializeFields());
        $class->appendChild($this->serializeSuperclasses());

        return $class;
    } 
    
    private function serializeFields() {
        $fields = $this->domDocument->createElement("fields");
        
        foreach ($this->pClass->getFields() as $field) {
            $fields->appendChild(
                $this->domDocument->createElement("field", $field)
            );
        }
        
        return $fields;
    }
    
     private function serializeSuperclasses() {
        $superclasses = $this->domDocument->createElement("superclasses");
        
        foreach ($this->pClass->getSuperclasses() as $superclass) {
            $superclasses->appendChild(
                $this->domDocument->createElement("superclass", $superclass)
            );
        }
        
        return $superclasses;
    }
    
}
