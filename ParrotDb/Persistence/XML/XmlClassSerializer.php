<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\ObjectModel\PClass;

/**
 * Description of XmlClassSerializer
 *
 * @author J. Baum
 */
class XmlClassSerializer {
    
    protected $domDocument;
    
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
     * Serializes a PClass as XML.
     * 
     * @param PClass $pClass
     * @return \DOMDocument
     */
    public function serializeClass(PClass $pClass) {
        
        $class = $this->domDocument->createElement( "class" );
        $class->appendChild($this->domDocument->createElement("name", $pClass->getName()));
        $class->appendChild($this->serializeFields($pClass));
        $class->appendChild($this->serializeSuperclasses($pClass));

        return $class;
    } 
    
    private function serializeFields(PClass $pClass) {
        $fields = $this->domDocument->createElement("fields");
        
        foreach ($pClass->getFields() as $field) {
            $fields->appendChild($this->domDocument->createElement("field", $field));
        }
        
        return $fields;
    }
    
     private function serializeSuperclasses(PClass $pClass) {
        $superclasses = $this->domDocument->createElement("superclasses");
        
        foreach ($pClass->getSuperclasses() as $superclass) {
            $superclasses->appendChild($this->domDocument->createElement("superclass", $superclass));
        }
        
        return $superclasses;
    }
    
}
