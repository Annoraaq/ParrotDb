<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Persistence\Deserializer;
use \ParrotDb\Utils\PXmlUtils;

/**
 * The XmlClassDeserializer handles the deserialization of an xml file into
 * a PClass object
 *
 * @author J. Baum
 */
class XmlClassDeserializer implements Deserializer {
    
    private $domDocument;
    private $classElem;
    
    /**
     * @param \DOMDocument $domDocument
     */
    public function __construct(\DOMDocument $domDocument) {
        $this->domDocument = $domDocument;
    }
    
    /**
     * @return \ParrotDb\ObjectModel\PClass
     */
    public function deserialize() {
        $this->classElem = PXmlUtils::firstElemByTagName(
            $this->domDocument->firstChild,
            "class"
        );
        
        $pClass = $this->createPClass();
        
        $fieldsElem = PXmlUtils::firstElemByTagName($this->classElem, "fields");
        foreach ($fieldsElem->getElementsByTagName("field") as $field) {
            $pClass->addField($field->nodeValue);
        }
        
        $superclassesElem = PXmlUtils::firstElemByTagName($this->classElem, "superclasses");
        foreach ($superclassesElem->getElementsByTagName("superclass") as $superclass) {
            $pClass->addSuperclass($superclass->nodeValue);
        }
        
        return $pClass;
    }
    
    private function createPClass() {
        return new \ParrotDb\ObjectModel\PClass(
            PXmlUtils::firstElemByTagName(
                $this->classElem,
                "name"
            )->nodeValue
        );
    }
    
}
